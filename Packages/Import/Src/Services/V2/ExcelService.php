<?php

namespace Packages\Import\Services\V2;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Packages\Product\Models\V2\Product;
use Packages\Product\Models\V2\ProductVariation;
use Packages\Import\Contracts\V2\ExcelHelpers;
use Packages\Import\Repositories\Eloquent\V2\ExcelRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExcelService
{
    use ExcelHelpers;

    protected $excelRepository;

    public function __construct(ExcelRepository $excelRepository)
    {
        $this->excelRepository = $excelRepository;
    }

    public function downloadSampleExcel($data)
    {
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        $productIds = isset($data['product_ids']) ? $data['product_ids'] : [];
        $products   = $this->excelRepository->getProductsByIds($productIds);

        $excelData = [
            [
                'product_id', 'product_type', 'variant_id', 'title', 'short_description',
                'description', 'price', 'current_stock', 'sku', 
                'productSize_length', 'productSize_width', 'productSize_height', 'dimention_type'
            ],
        ];

        foreach ($products as $product) {
            
            $excelData[] = [
                $product->product_id,
                $product->product_type,
                '',
                $product->title,
                $product->short_description,
                $product->description,
                $product->price,
                $product->current_stock,
                $product->sku,
                $product->productSize_length,
                $product->productSize_width,
                $product->productSize_height,
                $product->dimention_type,
            ];

            if ($product->product_type === 'variant') {
                $variants = DB::table('product_variation')
                    ->where('product_id', $product->product_id)
                    ->get();

                foreach ($variants as $variant) {
                    $excelData[] = [
                        $product->product_id,
                        '',
                        $variant->variation_id,
                        '', '', '', $variant->price,
                        $variant->current_stock,
                        $variant->sku,
                        '','', '', '', '', ''
                    ];
                }
            }
        }

        $filename = 'products.xlsx';

        return Excel::download(
            new class($excelData) implements FromArray {
                private $data;
                public function __construct(array $data) { $this->data = $data; }
                public function array(): array { return $this->data; }
            },
            $filename,
            \Maatwebsite\Excel\Excel::XLSX,
            $headers
        );
    }

    public function bulkUpdateProducts($file)
    {
        $userId = auth()->user()->user_id;
        $store  = ExcelHelpers::getAccessibleStore($userId);
        $storeId = $store?->store_id;

        if (!$storeId) {
            throw new \Exception("No accessible store found for the user.");
        }

        $rows = Excel::toArray([], $file)[0];

        $errors = [];
        $updatedCount = 0;

        $productUpserts   = [];
        $variationUpserts = [];
        $parentProductIds = [];
        $searchableIds    = [];

        foreach ($rows as $index => $row) {

            if ($index === 0) continue;

            $productId     = $this->cleanNumeric($row[0] ?? null, 'product_id', $index);
            $productType   = $row[1] ?? null;
            $variantId     = $row[2] ?? null;
            $price         = $this->cleanNumeric($row[6] ?? null, 'price', $index);
            $stock         = $this->cleanNumeric($row[7] ?? null, 'current_stock', $index);
            $sku           = $row[8] ?? null;

            if (!$productId) {
                $errors[] = "Row $index: Missing product_id.";
                continue;
            }

            /**
             * --------------------
             * VARIATIONS
             * --------------------
             */
            if (!empty($variantId)) {
                // Prepare base data
                $variantUpdateData = [
                    'price'         => $price,
                    'current_stock' => $stock,
                    'sku'           => $sku,
                ];
                // Call discount logic
                $discountedData = $this->excelRepository->updateVariationWithDiscount(
                    $productId,
                    $variantId,
                    $variantUpdateData,
                    $storeId
                );
                $variationUpserts[] = array_merge([
                    'product_id'    => $productId,
                    'variation_id'  => $variantId,
                    'updated_at'    => now(),
                ], $discountedData);
                $parentProductIds[] = $productId;
                $searchableIds[] = $productId;
                $updatedCount++;
                continue;
            }
            /**
             * --------------------
             * SIMPLE PRODUCTS
             * --------------------
             */
             if ($productType === 'simple') {

               $productData = [
                    'product_id'         => $productId,
                    'store_id'           => $storeId,
                    'product_type'       => 'simple',

                    'title'              => $this->cleanString($row[3] ?? null, 'title', $index) !== '' ? $row[3] : DB::raw('title'),
                    'short_description'  => $this->cleanString($row[4] ?? null, 'short_description', $index) !== '' ? $row[4] : DB::raw('short_description'),
                    'description'        => $this->cleanString($row[5] ?? null, 'description', $index) !== '' ? $row[5] : DB::raw('description'),

                    'price'              => $price !== null ? $price : DB::raw('price'),
                    'current_stock'      => $stock !== null ? $stock : DB::raw('current_stock'),
                    'sku'                => $sku !== '' ? $sku : DB::raw('sku'),

                    'productSize_length' => $this->cleanString($row[9] ?? null, 'productSize_length', $index) !== '' ? $row[9] : DB::raw('productSize_length'),
                    'productSize_width'  => $this->cleanString($row[10] ?? null, 'productSize_width', $index) !== '' ? $row[10] : DB::raw('productSize_width'),
                    'productSize_height' => $this->cleanString($row[11] ?? null, 'productSize_height', $index) !== '' ? $row[11] : DB::raw('productSize_height'),
                    'dimention_type'     => $this->cleanString($row[12] ?? null, 'dimention_type', $index) !== '' ? $row[12] : DB::raw('dimention_type'),

                    'updated_at'         => now(),
                ];

               
                $productUpserts[] =
                    $this->excelRepository->updateProduct($productId, $productData);

                $searchableIds[] = $productId;
                $updatedCount++;
            }
        }

        /**
         * --------------------
         * SINGLE DB WRITE
         * Disable Scout auto-sync during bulk writes to prevent duplicate Algolia entries.
         * Products are explicitly indexed in the ASYNC SEARCH INDEX block below.
         * --------------------
         */
        Product::withoutSyncingToSearch(function () use ($productUpserts, $variationUpserts, $parentProductIds, $storeId) {
            DB::transaction(function () use ($productUpserts, $variationUpserts) {

                if (!empty($variationUpserts)) {
                    foreach ($variationUpserts as $variation) {
                        $productId = $variation['product_id'];
                        $variationId = $variation['variation_id'];
                        $updateData = $variation;
                        unset($updateData['product_id'], $updateData['variation_id']);
                        // Only keep fields that exist in ProductVariation table
                        $allowed = ['price', 'current_stock', 'sku', 'sale_price', 'discount_percentage', 'updated_at'];
                        $updateData = array_intersect_key($updateData, array_flip($allowed));
                        ProductVariation::where('product_id', $productId)
                            ->where('variation_id', $variationId)
                            ->update($updateData);
                    }
                }

                if ($productUpserts) {
                    foreach ($productUpserts as $product) {
                        $productId = $product['product_id'];
                        $storeId = $product['store_id'];
                        $updateData = $product;
                        unset($updateData['product_id'], $updateData['store_id']);
                        Product::where('product_id', $productId)
                            ->where('store_id', $storeId)
                            ->update($updateData);
                    }
                }
            });

       

        /* ===============================
            | UPDATE PARENT PRODUCTS
            ===============================*/
            foreach (array_unique($parentProductIds) as $productId) {

                $variations = ProductVariation::where('product_id', $productId)->get();
                if ($variations->isEmpty()) continue;

                // Pass an object with 'variations' property
                $parentProduct = (object)[
                    'variations' => $variations
                ];
                $minPrice = $variations->min('price');
                $totalStock = $variations->sum('current_stock');
                $parentPriceData = $this->excelRepository->updateParentProductPrice($parentProduct, $minPrice);
                $parentPriceData['current_stock'] = $totalStock;

                Product::where('product_id', $productId)
                    ->where('store_id', $storeId)
                    ->update($parentPriceData);
            }
        });
        /**
         * --------------------
         * ASYNC SEARCH INDEX
         * --------------------
         */
        if (!empty($searchableIds)) {
            dispatch(function () use ($searchableIds) {
            $products = Product::whereIn('product_id', array_unique($searchableIds))->get();
                $products->searchable();
            })->afterResponse();
        }

        return [
            'updated_count' => $updatedCount,
            'errors' => $errors,
        ];
    }

}
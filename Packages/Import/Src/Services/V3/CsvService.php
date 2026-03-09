<?php

namespace Packages\Import\Services\V3;

use Illuminate\Http\JsonResponse;
use Packages\Import\Contracts\V3\CsvHelpers;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\ItemNotFoundException;
use Packages\Import\Repositories\Eloquent\V3\CsvRepository;


class CsvService
{
    use CsvHelpers;
    protected $csvRepository;

    public function __construct(CsvRepository $csvRepository)
    {
        $this->csvRepository = $csvRepository;
    }

    public function validateCsvFile($csvFile)
    {
        $handle = fopen($csvFile, 'r');
        $headers = fgetcsv($handle);
        $errors = [];
        $rows = [];
    
        while (($data = fgetcsv($handle)) !== false) {
            $row = array_combine($headers, $data);
            $slug = $this->normalization($row['Handle']);
            $productType = $this->normalization($row['Product Type']);
        
            // Validate row data
            $validation = $this->validateRowData($row);
            if (!$validation['valid']) {
                $errors[] = $validation['message'];
                continue;
            }
        
            // Check if product exists
            $product = $this->getProductBySlug($slug);
        
            if ($product instanceof JsonResponse) {
                $productData = $product->getData();
                if (!$productData->status) {
                    $errors[] = $productData->message;
                    continue;
                }
            }
            
            if ($product && $product->product_type !== $productType) {
                $errors[] = "Invalid product type for slug: {$slug}. Expected: {$product->product_type}, Got: {$productType}";
                continue;
            }

            if ($validation['valid'] && !($product instanceof JsonResponse)) {
                $rows[] = $row;
            }
        }
        
        fclose($handle);
    
        if (!empty($errors)) {
            throw new InvalidArgumentException(json_encode($errors));
        }
    
        return $rows;
    }

    public function updateProducts($rows) 
    {
        $updates = [];
        $errors = [];
    
        foreach ($rows as $row) {
            try {
                // Normalize the product type
                $row['Product Type'] = $this->normalization($row['Product Type']);
    
                switch ($row['Product Type']) {
                    case 'simple':
                        $updates[] = fn() => $this->updateCsvSimpleProduct($row);
                        break;
                    case 'variant':
                        $updates[] = fn() => $this->updateCsvVariableProduct($row);
                        break;
                    default:
                        $errors[] = "Invalid product type for Handle: {$row['Handle']}";
                }
            } catch (Exception $e) {

                $errors[] = $e->getMessage();
            }
        }
    
        if (!empty($errors)) {
            return [$updates, $errors]; 
        }
    
        DB::beginTransaction();
    
        try {
            foreach ($updates as $update) {
                $update();
            }
    
            DB::commit();
            return [$updates, $errors];
        } catch (Exception $e) {

            DB::rollBack();
            $errors[] = 'Some updates failed: ' . $e->getMessage();
            return [$updates, $errors]; 
        }
    }

    public function updateCsvSimpleProduct(array $data)
    {
        $slug = $this->normalization($data['Handle']);
        $product = $this->getProductBySlug($slug);

        if (!$product) {
            throw new ItemNotFoundException("Simple product with handle {$slug} not found.");
        }

        $this->csvRepository->updateSimpleProductPriceAndStock($product, $data);
        $this->csvRepository->applyDiscount($product);

        return $product;
    }

    public function updateCsvVariableProduct(array $data)
    {
        $slug = $this->normalization($data['Handle']);
        $product = $this->getProductBySlug($slug);

        if (!$product) {
            throw new ItemNotFoundException("Variable product with handle {$slug} not found.");
        }

        $this->csvRepository->updatePriceAndStock($product, $data);
        $this->csvRepository->applyDiscount($product);

        return $product;
    }

}

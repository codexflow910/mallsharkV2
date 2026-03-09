<?php

namespace Packages\Product\Services\V2\ProductTypes;

use Illuminate\Support\Facades\DB;
use Packages\Product\Models\V2\Product;
use Packages\Product\Models\V2\ProductCustomization;
use Packages\Product\Models\V2\CustomImageSetting;
use Packages\Product\Models\V2\CustomTextStyle;
use Packages\Product\Models\V2\CustomTextStyleOption;
use Packages\Product\Services\ProductService;
use Packages\Product\Models\V2\CustomTextCharacter;
use Packages\Product\Contracts\V2\ProductHelpers;

class CustomProductService extends ProductService
{
    public function __construct(
        protected SimpleProductService $simpleService,
        protected VariableProductService $variantService
    ) {}

    public function storeSimple(array $data): Product
    {
        return DB::transaction(function () use ($data) {

            // ✅ REUSE existing logic
            $product = $this->simpleService->store($data);

            // ✅ ADD customization
            $this->storeCustomization($product, $data);

            return $product;
        });
    }

    public function storeVariant(array $data): Product
    {
        return DB::transaction(function () use ($data) {

            // ✅ REUSE existing logic
            $product = $this->variantService->store($data);

            // ✅ ADD customization
            $this->storeCustomization($product, $data);

            return $product;
        });
    }

    public function updateSimple(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = $this->simpleService->update($data);
            $this->updateCustomization($product, $data);
            // Delete tiers if any
            ProductHelpers::deleteSimpleProductTiersData($product->product_id);
            return $product;
        });
    }

    public function updateVariant(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = $this->variantService->update($data);
            $this->updateCustomization($product, $data);
            // Delete tiers if any
            ProductHelpers::deleteVariableProductTiersData($product->product_id);
            return $product;
        });
    }

    protected function updateCustomization(Product $product, array $data): void
    {
        // ===============================
        // PRODUCT CUSTOMIZATION
        // ===============================
        $customization = ProductCustomization::where('product_id', $product->product_id)->first();

        if ($customization) {
            $customization->update([
                'allow_text'  => $data['allow_text'] ?? $customization->allow_text,
                'allow_image' => $data['allow_image'] ?? $customization->allow_image,
                'price'       => $data['custom_price'] ?? $customization->price,
            ]);
        } else {
             $customization = ProductCustomization::create([
                'product_id'  => $product->product_id,
                'allow_text'  => $data['allow_text'] ?? false,
                'allow_image' => $data['allow_image'] ?? false,
                'price'       => $data['custom_price'] ?? 0,
            ]);
        }

        // ===============================
        // IMAGE SETTINGS (UPDATE ONLY)
        // ===============================
        if (!empty($data['allow_image'])) {

            $img = CustomImageSetting::where('product_id', $product->product_id)->first();

            if ($img) {
                $img->update([
                    'min_width'  => $data['image_settings']['min_width'] ?? $img->min_width,
                    'min_height' => $data['image_settings']['min_height'] ?? $img->min_height,
                ]);
            } else {
                $img = CustomImageSetting::create([
                    'product_id' => $product->product_id,
                    'min_width'  => $data['image_settings']['min_width'] ?? null,
                    'min_height' => $data['image_settings']['min_height'] ?? null,
                ]);
            }

        } else {
            // allowed: delete when explicitly disabled
            CustomImageSetting::where('product_id', $product->product_id)->delete();
        }

        // ===============================
        // TEXT STYLES (UPDATE ONLY)
        // ===============================
        if (!empty($data['allow_text']) && !empty($data['text_styles'])) {

            $existingStyles = CustomTextStyle::where('product_id', $product->product_id)->get();
            $existingStylesById = $existingStyles->keyBy('id');
            $existingStylesByName = $existingStyles->keyBy(function ($s) {
                return mb_strtolower($s->style_name);
            });

            $incomingStyleIds = [];

            foreach ($data['text_styles'] as $styleData) {
                $styleName = trim($styleData['style_name'] ?? '');
                $styleId = $styleData['id'] ?? null;

                // find existing style by id, then by name — 
                if ($styleId && isset($existingStylesById[$styleId])) {
                    $style = $existingStylesById[$styleId];
                } elseif ($styleName !== '' && isset($existingStylesByName[mb_strtolower($styleName)])) {
                    $style = $existingStylesByName[mb_strtolower($styleName)];
                } else {
                  // CREATE new style
                if ($styleName === '') continue;

                $style = CustomTextStyle::create([
                    'product_id' => $product->product_id,
                    'style_name' => $styleName,
                ]);
                }

                // update style name if changed
                if ($styleName !== '' && $style->style_name !== $styleName) {
                    $style->style_name = $styleName;
                    $style->save();
                }

                $incomingStyleIds[] = $style->id;

                // manage options for this style
                $existingOptions = CustomTextStyleOption::where('style_id', $style->id)->get();
                $existingOptionsById = $existingOptions->keyBy('id');
                $existingOptionsByValue = $existingOptions->keyBy(function ($o) {
                    return mb_strtolower($o->value);
                });

                $incomingOptionIds = [];

                foreach ($styleData['options'] as $optionData) {
                    $optId = $optionData['id'] ?? null;
                    $optValue = trim($optionData['value'] ?? '');

                    if ($optId && isset($existingOptionsById[$optId])) {
                        $opt = $existingOptionsById[$optId];
                        if ($opt->value !== $optValue && $optValue !== '') {
                            $opt->value = $optValue;
                            $opt->save();
                        }
                        $incomingOptionIds[] = $opt->id;
                        continue;
                    }

                    if ($optValue !== '' && isset($existingOptionsByValue[mb_strtolower($optValue)])) {
                        $opt = $existingOptionsByValue[mb_strtolower($optValue)];
                        // value matches existing option — nothing to change
                        $incomingOptionIds[] = $opt->id;
                        continue;
                    }

                  if ($optValue === '') {
                        continue;
                    }

                    $opt = CustomTextStyleOption::create([
                        'style_id' => $style->id,
                        'value'    => $optValue,
                    ]);

                    $incomingOptionIds[] = $opt->id;
                }
            }

            // update/create character limit if provided
            $characters = $data['buyer_text_limit'] ?? null;
            if ($characters !== null) {
                $this->upsertTextCharacter($product, $characters);
            }
        } else {
            // text disabled → allowed delete
            CustomTextStyleOption::whereIn(
                'style_id',
                CustomTextStyle::where('product_id', $product->product_id)->pluck('id')
            )->delete();

            CustomTextStyle::where('product_id', $product->product_id)->delete();
            // delete character limit record when text disabled
            CustomTextCharacter::where('product_id', $product->product_id)->delete();
        }
    }


    protected function storeTextStyles(Product $product, array $styles, $characters): void
    {
    foreach ($styles as $style) {
        
        // 1️⃣ Create style
        $textStyle = CustomTextStyle::create([
            'product_id' => $product->product_id,
            'style_name' => $style['style_name'],   // e.g. Color, Font
        ]);

        // 2️⃣ Create options for that style
        foreach ($style['options'] as $option) {
            CustomTextStyleOption::create([
                'style_id' => $textStyle->id,
                'value'  => $option['value'],        // e.g. Red, Arial
            ]);
        }
    }
        // 3️⃣ Store character limit associated with product
        CustomTextCharacter::create([
            'product_id' => $product->product_id,
            'characters_limit' => $characters,
        ]);
}
    protected function storeCustomization(Product $product, array $data): void
    {
        ProductCustomization::create([
            'product_id'  => $product->product_id,
            'allow_text'  => $data['allow_text'] ?? false,
            'allow_image' => $data['allow_image'] ?? false,
            'price'       => $data['custom_price'] ?? 0,
        ]);

        if (!empty($data['allow_image'])) {
            CustomImageSetting::create([
                'product_id'        => $product->product_id,
                'min_width'         => $data['image_settings']['min_width'] ?? null,
                'min_height'        => $data['image_settings']['min_height'] ?? null,
            ]);
        }

        if (!empty($data['allow_text'])) {
            $characters = $data['buyer_text_limit'] ?? null;
            $this->storeTextStyles($product, $data['text_styles'] ?? [], $characters);
        }
    }

    protected function upsertTextCharacter(Product $product, $characters = null): void
    {
        $record = CustomTextCharacter::where('product_id', $product->product_id)->first();
        if ($record) {
            $record->update(['characters_limit' => $characters]);
        }else {
            CustomTextCharacter::create([
                'product_id' => $product->product_id,
                'characters_limit' => $characters,
            ]);
        }
    }
}

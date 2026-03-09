<?php

namespace Packages\Import\Contracts\V4;

use Packages\Product\Models\V4\Product;
use Exception;
use Illuminate\Support\ItemNotFoundException;

trait CsvHelpers
{
    use BaseApiResponse;

    public function getProductBySlug(string $slug)
    {
        try {
            $product = Product::where('slug', $slug)->first();
    
            if ($product == null) {
                throw new ItemNotFoundException("Product not found: {$slug}");
            }
    
            return $product; 
            
        } catch (Exception $e) {
            return $this->errorResponse("Error fetching product by slug: '{$slug}'. Porduct not found.");
        }
    }    

    public function validateRowData($row)
    {
        $price = isset($row['Variant Price']) ? trim($row['Variant Price']) : '';
        $stock = isset($row['Variant Inventory']) ? trim($row['Variant Inventory']) : '';
    
        // Validate price
        if ($price !== '' && (!is_numeric($price) || $price <= 0)) {
            return ['valid' => false, 'message' => "Invalid price for Handle: {$row['Handle']} (Price: {$price})"];
        }
    
        // Validate stock
        if ($stock !== '' && (!is_numeric($stock) || $stock < 0)) {
            return ['valid' => false, 'message' => "Invalid inventory for Handle: {$row['Handle']} (Inventory: {$stock})"];
        }
    
        return ['valid' => true]; 
    }

    public function normalization($value)
    {
        return strtolower(trim($value));
    }
}

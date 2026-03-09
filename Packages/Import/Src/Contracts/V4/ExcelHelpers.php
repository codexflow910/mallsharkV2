<?php

namespace Packages\Import\Contracts\V4;

use Packages\Product\Models\V4\Product;
use Exception;
use Illuminate\Support\ItemNotFoundException;
use Packages\Store\Models\V4\Store;
use Illuminate\Support\Facades\DB;

trait ExcelHelpers
{
    public function cleanInt($value)
    {
        return is_numeric($value) ? (int) $value : null;
    }

    public function cleanFloat($value)
    {
        return is_numeric($value) ? (float) $value : null;
    }

    public function cleanBool($value)
    {
        return in_array(strtolower($value), ['1','true','yes']) ? 1 :
            (in_array(strtolower($value), ['0','false','no']) ? 0 : null);
    }
    public function cleanNumeric($value, $fieldName, $rowNumber)
    {
    if ($value === null || trim($value) === '') {
        throw new \Exception("Row $rowNumber: '$fieldName' cannot be empty.");
    }

    // Remove all characters except digits and dots
    $clean = preg_replace('/[^0-9.]/', '', $value);

    // Handle empty or invalid cleaned values
    if ($clean === '' || $clean === '.' || $clean === '..') {
        throw new \Exception("Row $rowNumber: '$fieldName' contains invalid numeric value.");
    }

    // Replace multiple dots with a single dot
    $clean = preg_replace('/\.{2,}/', '.', $clean);

    // Convert to number
    $num = floatval($clean);

    // Validation range (only for current stock field)
    if ($fieldName === 'current_stock') {
        if ($num < 0) {
             throw new \Exception("Row $rowNumber: '$fieldName' cannot be negative.");
        }

        if ($num > 999) {
            throw new \Exception("Row $rowNumber: '$fieldName' cannot exceed 999.");
        }
    }

    return $num;
    }
    public static function getAccessibleStore($userId)
    {
        $store = Store::where('user_id', $userId)->first();

        if (!$store) {
            $pivot = DB::table('store_user')->where('user_id', $userId)->first();

            if ($pivot) {
                $store = Store::find($pivot->store_id);
            }
        }

        return $store;
    }
    public function cleanString($value, $fieldName, $rowNumber)
    {
        if ($value === null || trim($value) === '') {
        throw new \Exception("Row $rowNumber: '$fieldName' cannot be empty.");
    }
    }
}
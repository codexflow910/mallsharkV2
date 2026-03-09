<?php

namespace App\Models;

use Packages\Product\Models\V2\Product as V2Product;

/**
 * Product Model
 *
 * This class extends the V2 Product model to maintain backward compatibility.
 * All product functionality is centralized in Packages\Product\Models\V2\Product.
 *
 * @see \Packages\Product\Models\V2\Product
 */
class Product extends V2Product
{
    // All functionality is inherited from V2 Product model
}

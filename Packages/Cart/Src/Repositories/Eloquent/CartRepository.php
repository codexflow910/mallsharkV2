<?php

namespace Packages\Cart\Repositories\Eloquent;

use Exception;
use Packages\Cart\Models\ProductCart;
use Packages\Product\Models\ProductVariation;
use Packages\Cart\Contracts\CartRepositoryInterface;

class CartRepository implements CartRepositoryInterface
{

    protected $model;
    protected $productVariationModel;

    public function __construct(ProductCart $model, ProductVariation $productVariationModel)
    {
        $this->model = $model;
        $this->productVariationModel = $productVariationModel;
    }
    public function saveToCart($data)
    {
        try {
            return $this->model->create($data);
        } catch (Exception $e) {
            throw new Exception('Failed to save cart item: ' . $e->getMessage());
        }
    }


    public function getCartProduct($id, $isGuest, $productId, $variationId = null)
    {
        $query =    $this->model->where('product_id', $productId);

        if ($variationId) {
            $query->where('variation_id', $variationId);
        }

        if ($isGuest) {
            $query->where('random_key', $id);
        } else {
            $query->where('user_id', $id);
        }

        return $query->first();
    }


    public function getVariationsInfo(array $options, int $productId)
    {
        $query = $this->productVariationModel->where('product_id', $productId);

        // Dynamically apply filters
        if (!empty($options[0])) {
            $query->where('option1', $options[0]);
        }

        if (!empty($options[1])) {
            $query->where('option2', $options[1]);
        }

        if (!empty($options[2])) {
            $query->where('option3', $options[2]);
        }

        return $query->first();
    }

    public function getVariation($id)
    {
        return $this->productVariationModel->find($id);
    }

    public function updateQuantity(array $conditions, int $quantity)
    {
        return $this->model->where($conditions)->update(['product_quantity' => $quantity]);
    }

    public function getCartItem(array $conditions)
    {
        return $this->model->where($conditions)->first();
    }

    public function deleteCart($cartId)
    {
        return $this->model->where('cart_id', $cartId)->delete();
    }

    public function toggleCart($cartId, $isCheck)
    {
        return $this->model->where('cart_id', $cartId)->update(['is_check' => $isCheck]);
    }

    public function getCart($data)
    {
        $query = $this->model->with(['product', 'variation', 'store', 'product.store']);
        
        if ($data['guest'] === false && !empty($data['id'])) {
            $query->where('user_id', $data['id']);
        } elseif ($data['guest'] === true && !empty($data['id'])) {
            $query->where('random_key', $data['id']);
        }
    
        return $query->get();
    }
}

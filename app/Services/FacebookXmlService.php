<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Collection;

class FacebookXmlService
{
    /**
     * Generate Facebook Product Catalog XML for multiple stores
     */
    public function generateXml(Collection $products): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss xmlns:g="http://base.google.com/ns/1.0" version="2.0"></rss>');
        $channel = $xml->addChild('channel');

        // Channel information
        $channel->addChild('title', htmlspecialchars('Mallshark Product Feed'));
        $channel->addChild('link', url('/'));
        $channel->addChild('description', htmlspecialchars('Multi-store product catalog feed for Facebook'));

        // Add products
        foreach ($products as $product) {
            $this->addProductToXml($channel, $product);
        }

        // Format XML with indentation
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());

        return $dom->saveXML();
    }

    /**
     * Add a single product to the XML feed
     */
    private function addProductToXml(\SimpleXMLElement $channel, Product $product): void
    {
        $item = $channel->addChild('item');

        // Get store information
        $store = $product->store ?? Store::find($product->store_id);

        // Required fields for Facebook Product Catalog
        $this->addChildWithCDATA($item, 'g:id', $product->product_id, 'http://base.google.com/ns/1.0');
        $this->addChildWithCDATA($item, 'g:title', substr($product->title, 0, 150), 'http://base.google.com/ns/1.0');

        // Description (required, max 5000 chars)
        $description = $product->description ?? $product->short_description ?? $product->title;
        $description = strip_tags($description);
        $description = substr($description, 0, 5000);
        $this->addChildWithCDATA($item, 'g:description', $description, 'http://base.google.com/ns/1.0');

        // Product Link (required)
        $this->addChildWithCDATA($item, 'g:link', url('/product/' . $product->slug), 'http://base.google.com/ns/1.0');

        // Image URL (required)
        $imageUrl = $this->getProductImage($product);
        $this->addChildWithCDATA($item, 'g:image_link', $imageUrl, 'http://base.google.com/ns/1.0');

        // Additional images
        $additionalImages = $this->getAdditionalImages($product);
        foreach ($additionalImages as $index => $additionalImage) {
            if ($index < 10) { // Facebook allows up to 10 additional images
                $this->addChildWithCDATA($item, 'g:additional_image_link', $additionalImage, 'http://base.google.com/ns/1.0');
            }
        }

        // Price (required)
        $price = $product->price;
        $this->addChildWithCDATA($item, 'g:price', number_format($price, 2, '.', '') . ' USD', 'http://base.google.com/ns/1.0');

        // Sale price (if applicable)
        if ($product->on_sale && $product->sale_price > 0 && $product->sale_price < $product->price) {
            $this->addChildWithCDATA($item, 'g:sale_price', number_format($product->sale_price, 2, '.', '') . ' USD', 'http://base.google.com/ns/1.0');
        }

        // Availability (required)
        $availability = $product->current_stock > 0 ? 'in stock' : 'out_of_stock';
        $this->addChildWithCDATA($item, 'g:availability', $availability, 'http://base.google.com/ns/1.0');

        // Condition (required)
        $this->addChildWithCDATA($item, 'g:condition', 'new', 'http://base.google.com/ns/1.0');

        // Brand (recommended)
        if ($store) {
            $this->addChildWithCDATA($item, 'g:brand', $store->store_name, 'http://base.google.com/ns/1.0');
        }

        // MPN or GTIN (at least one is recommended)
        $this->addChildWithCDATA($item, 'g:mpn', 'MPN-' . $product->product_id, 'http://base.google.com/ns/1.0');

        // Product Type (recommended)
        if ($product->product_type) {
            $productType = ucfirst($product->product_type);
            $this->addChildWithCDATA($item, 'g:product_type', $productType, 'http://base.google.com/ns/1.0');
        }

        // Google Product Category (recommended)
        $googleCategory = $this->getGoogleProductCategory($product);
        $this->addChildWithCDATA($item, 'g:google_product_category', $googleCategory, 'http://base.google.com/ns/1.0');

        // Custom labels for grouping/filtering in Facebook
        if ($store) {
            $this->addChildWithCDATA($item, 'g:custom_label_0', $store->store_name, 'http://base.google.com/ns/1.0');
        }

        // Stock quantity
        if ($product->current_stock > 0) {
            $this->addChildWithCDATA($item, 'g:inventory', $product->current_stock, 'http://base.google.com/ns/1.0');
        }

        // Shipping weight (if available)
        if (isset($product->weight) && $product->weight > 0) {
            $this->addChildWithCDATA($item, 'g:shipping_weight', $product->weight . ' lb', 'http://base.google.com/ns/1.0');
        }

        // Item group ID for variants (if applicable)
        if ($product->product_type === 'variant' && isset($product->parent_id)) {
            $this->addChildWithCDATA($item, 'g:item_group_id', $product->parent_id, 'http://base.google.com/ns/1.0');
        }
    }

    /**
     * Add child element with CDATA to handle special characters
     */
    private function addChildWithCDATA(\SimpleXMLElement $parent, string $name, $value, string $namespace = null): \SimpleXMLElement
    {
        if ($namespace) {
            $child = $parent->addChild($name, null, $namespace);
        } else {
            $child = $parent->addChild($name);
        }

        // Create a DOM node from the SimpleXML element
        $dom = dom_import_simplexml($child);
        $domDocument = $dom->ownerDocument;

        // Sanitize value to ensure valid UTF-8
        $sanitizedValue = $this->sanitizeUtf8($value);

        // Add CDATA section
        $cdata = $domDocument->createCDATASection($sanitizedValue);
        $dom->appendChild($cdata);

        return $child;
    }

    /**
     * Sanitize string to ensure valid UTF-8 encoding
     */
    private function sanitizeUtf8($value): string
    {
        if (!is_string($value)) {
            $value = (string) $value;
        }

        // Remove invalid UTF-8 sequences
        $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');

        // Remove any remaining invalid characters
        $value = iconv('UTF-8', 'UTF-8//IGNORE', $value);

        // Remove control characters except newlines and tabs
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);

        return $value;
    }

    /**
     * Get product image URL
     */
    private function getProductImage(Product $product): string
    {
        if ($product->feature_image) {
            $imageUrl = getAttachmentUrlById($product->feature_image, 'medium');
            if ($imageUrl) {
                return $imageUrl;
            }
        }

        return url('/assets/images/placeholder.jpg');
    }

    /**
     * Get additional product images
     */
    private function getAdditionalImages(Product $product): array
    {
        $images = [];

        // You can customize this based on how your product gallery is stored
        // Example: if you have a gallery relationship or field
        if (isset($product->gallery) && is_array($product->gallery)) {
            foreach ($product->gallery as $imageId) {
                $imageUrl = getAttachmentUrlById($imageId, 'medium');
                if ($imageUrl) {
                    $images[] = $imageUrl;
                }
            }
        }

        return $images;
    }

    /**
     * Map product to Google Product Category
     * You should customize this based on your product categories
     */
    private function getGoogleProductCategory(Product $product): string
    {
        // Default category
        $defaultCategory = 'Apparel & Accessories';

        // You can add logic here to map your categories to Google's taxonomy
        // Example: check product categories and return appropriate Google category

        return $defaultCategory;
    }
}

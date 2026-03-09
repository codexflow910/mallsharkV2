<?php

namespace Packages\Import\Config\V2;

class CsvConstants
{


}

class ShopifyConstants
{

    const SHOPIFY_API_VERSION = '2023-01';
    const SHOPIFY_INVENTORY_UPDATE_URL = "/admin/api/" . self::SHOPIFY_API_VERSION . "/graphql.json";
    const API_ENDPOINT = "/admin/api/". self::SHOPIFY_API_VERSION ."/graphql.json";

    const PRODUCT_QUERY = '{
              products(first: 10) { 
                edges { 
                  cursor 
                  node { 
                    id 
                    title 
                    handle 
                    status  # Make sure this is included
                    totalInventory
                    totalVariants  # Ensure this is fetched
                    priceRangeV2 { 
                      maxVariantPrice { amount currencyCode }
                      minVariantPrice { amount currencyCode }
                    }
                    images(first: 1) { 
                      nodes { url } 
                    }
                    productCategory { 
                      productTaxonomyNode { 
                        id 
                        fullName 
                        isRoot 
                        name 
                        isLeaf 
                      } 
                    }
                    variants(first: 10) { 
                      edges { 
                        node { 
                          title 
                          sku 
                          price 
                          weight 
                          weightUnit 
                          inventoryQuantity
                          requiresShipping 
                        } 
                      } 
                    } 
                  } 
                } 
              }
            }
    ';

}

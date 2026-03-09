<?php
namespace Packages\Search\Contracts;

use App\Models\Product;
use App\Models\Store;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Http;
trait SearchHelpers
{

    protected function convert_to_slug($str)
 {
    

     $str = strtolower(trim($str));
     $str = preg_replace('/[^a-z0-9-]/', '-', $str);
     $str = preg_replace('/-+/', "-", $str);
     $slug = rtrim($str, '-');

     $result_slug = Product::where('slug', $slug)->get()->toArray();
     $getRecord = count($result_slug);
     if ($getRecord > 0) {
         $slug = $slug . '-' . rand(45, 568522);
     }
     return $slug;
 }

 function webPImages($path,$size){
    // dd($path);
    $response = Http::withOptions(['allow_redirects' => true])->get($path);

        // Check if the request was successful
        if (!$response->successful()) {
            return null; // Image not found or inaccessible
        }

        // Get the filename without extension
        $filename = pathinfo(parse_url($path, PHP_URL_PATH), PATHINFO_FILENAME) . '.webp';

        // Convert image to WebP
        $image = Image::make($response->body())->encode('webp', 80);

        // Define the WebP save path (same directory as original image)
        $webpPath = public_path('uploads/'.$size .'/'. $filename);

        // Save the WebP image
        $image->save($webpPath);

        // Return the WebP image URL
        return asset('uploads/'.$size .'/' . $filename);
    
}

}
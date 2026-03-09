<?php
namespace Packages\Search\Contracts\V4;

use App\Models\Product;
use App\Models\Store;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Packages\Product\Models\V4\Attachments;
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
    public static function getDistributorLogo($id, $size = 'media') {
        $global_obj = new Attachments();
        if(isset($id))$id=json_decode($id);
        if ($id != '' && is_int($id)) {
            $global_obj->id = $id;
            $global_obj->size = $size;
            return $global_obj->getDistributorLogo();
        } else {
            return 'Please add a valid id';
        }
    }
}
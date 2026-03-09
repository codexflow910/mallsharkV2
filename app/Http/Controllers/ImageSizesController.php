<?php

namespace App\Http\Controllers;

use Image;
use App\Models\User;
use App\Models\Store;
use App\Models\Product;
use App\Models\category;
use App\Models\AttachmentSizes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImageSizesController extends Controller
{
    public function StoreOptimizeImages()
    {
        // Define an array to store unique attachment IDs
        $allAttachmentIds = [];
    
        // Fetch all products with feature and gallery images
        $stores = User::select('user_avatar')->get()->toArray();
        // dd($stores);
        foreach ($stores as $store) {
            $storeImages = $store['user_avatar'];

           

    
            if (!empty($storeImages)) {
                // Add feature image ID to $allAttachmentIds
                $allAttachmentIds[] = $storeImages;
            }
        }
       
        // Remove duplicate attachment IDs
        $uniqueAttachmentIds = array_unique($allAttachmentIds);
        
        // Fetch attachment data for the unique IDs
        $attachmentData = DB::table('attachment')
        ->whereIn('attachment_id', $uniqueAttachmentIds)->get(['attachment_id', 'fileName', 'fileExtension']);
        // dd($attachmentData);
            
        
        // Initialize a counter
        $counter = count($attachmentData);
    
        // Create an associative array for attachment ID lookup
        $attachmentIds = [];
        
        // Loop through attachment data
        $i = 0;
        foreach ($attachmentData as $data) {
            // $filenameWithExtension = $data->fileName . '.' . $data->fileExtension;
            $filenameWithExtension = $data->fileName . '_'.$data->attachment_id.'.' . $data->fileExtension;
            $attachmentIds[$filenameWithExtension] = $data->attachment_id;
            // dump($filenameWithExtension);
            $this->getAttachmentId($filenameWithExtension);
            echo $i."<br />";
            $i++;
        }

    }
    public function index()
    {
        // Define an array to store unique attachment IDs
        $allAttachmentIds = [];
    
        // Fetch all products with feature and gallery images
        $products = Product::select('feature_image', 'gallery')->get()->toArray();
    
        foreach ($products as $product) {
            $featureImages = $product['feature_image'];
            $galleryImages = $product['gallery'];
    
            if ($galleryImages) {
                // Unserialize gallery images and merge them into $allAttachmentIds
                $gallery = unserialize($galleryImages);
                $allAttachmentIds = array_merge($allAttachmentIds, $gallery);
            }
    
            if (!empty($featureImages)) {
                // Add feature image ID to $allAttachmentIds
                $allAttachmentIds[] = $featureImages;
            }
        }
    
        // Remove duplicate attachment IDs
        $uniqueAttachmentIds = array_unique($allAttachmentIds);
    
        // Fetch attachment data for the unique IDs
        $attachmentData = DB::table('attachment')
            ->whereIn('attachment_id', $uniqueAttachmentIds)
            ->get(['attachment_id', 'fileName', 'fileExtension']);
        
        // Initialize a counter
        $counter = count($attachmentData);
    
        // Create an associative array for attachment ID lookup
        $attachmentIds = [];
        
        // Loop through attachment data
        // $i = 0;
        foreach ($attachmentData as $data) {
            $filenameWithExtension = $data->fileName . '_'.$data->attachment_id.'.' . $data->fileExtension;
            $attachmentIds[$filenameWithExtension] = $data->attachment_id;
        
            $this->getAttachmentId($filenameWithExtension);

        }

    }
    

    public function getAttachmentIdSingleImage($fileName)
    {
       try {
             $getDirectorywithSizes = $this->getAllAttachmentSizes();
             // dd($getDirectorywithSizes);
            foreach ($getDirectorywithSizes as $key => $value) {
                $directoryName = $value['name'];
                $width = $value['width'];
                $height = $value['height'];
                $destinationPath = public_path() . '/uploads/' . $directoryName;
            
                // dump($destinationPath);
                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0775, true);
                }

                $fullFilePath = public_path('uploads/media_old/' . $fileName);
                $newFilePath = $destinationPath . '/' . $fileName;

                // Check if a file with the same name already exists in the destination folder
                if (File::exists($newFilePath)) {
                    // If it exists, unlink (delete) it
                    unlink($newFilePath);
                }
                if (!File::exists($fullFilePath)) {
                            return "File does not exist: $fullFilePath";
                        }
               
                if ($width == 0 && $height == 0) {
                        $readImage = file_get_contents($fullFilePath);
                        file_put_contents($destinationPath . '/' . $fileName, $readImage);
                    } else {
                        // dd($fileName);
                        $img = Image::make($fullFilePath);
                        dump($img);
                
                        $img->resize($width, null, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($destinationPath . '/' . $fileName);
                }
            }
        } catch (\Throwable $e) {
           dd($e);
        }
    }
    public function getAttachmentId($fileName)
    {
       try {
             $getDirectorywithSizes = $this->getAllAttachmentSizes();
             // dd($getDirectorywithSizes);
            foreach ($getDirectorywithSizes as $key => $value) {
                $directoryName = $value['name'];
                $width = $value['width'];
                $height = $value['height'];
                $destinationPath = public_path() . '/uploads/' . $directoryName;
            
                // dump($destinationPath);
                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0775, true);
                }

                $fullFilePath = public_path('uploads/media_old/media/' . $fileName);
                dump($fullFilePath);
                $newFilePath = $destinationPath . '/' . $fileName;

                // Check if a file with the same name already exists in the destination folder
                if (File::exists($newFilePath)) {
                    // If it exists, unlink (delete) it
                    unlink($newFilePath);
                }
                if (!File::exists($fullFilePath)) {
                    return "File does not exist: $fullFilePath";
                }
               
                if ($width == 0 && $height == 0) {
                        $readImage = file_get_contents($fullFilePath);
                        file_put_contents($destinationPath . '/' . $fileName, $readImage);
                    } else {
                        $img = Image::make($fullFilePath);
                        dump($img);
                
                        $img->resize($width, $height, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($destinationPath . '/' . $fileName);
                }
            }
        } catch (\Throwable $e) {
           dd($e);
        }
    }
    public function getAllAttachmentSizes()
    {
        $AllSize = new AttachmentSizes();
        $getDirectorywithSizes = $AllSize->all()->toArray();
        if (!empty($getDirectorywithSizes)) {
            foreach ($getDirectorywithSizes as $key => $value) {

                $directoryName = $value['name'];
                $path = public_path() . '/uploads/' . $directoryName;
                if (!File::exists($path)) {
                    File::makeDirectory($path, 0775, true);
                }
            }
        }
        return $getDirectorywithSizes;
    }
}

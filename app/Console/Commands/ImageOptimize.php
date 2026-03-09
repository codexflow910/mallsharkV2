<?php

namespace App\Console\Commands;

use Image;
use App\Models\Product;
use App\Models\category;
use App\Models\AttachmentSizes;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImageOptimize extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'image:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Image optimization of exist folder';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $allAttachmentIds = [];
    
        // Fetch all products with feature and gallery images
        $stores = category::select('image_id')->get()->toArray();
        // dd($stores);
        foreach ($stores as $store) {
            $storeImages = $store['image_id'];

           

    
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
            echo $i." ";
            $i++;
        }
     
         // Output the counter
        
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
                       
                
                        $img->resize($width, null, function ($constraint) {
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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use File;
use Image;
use Session;
use App\Models\Attachments;
use App\Models\AttachmentSizes;

class AttachmentController extends Controller {

    public function generateRandomString($length) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxy';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function uploadTempFiles($files, $type, $path) {
        $itemArray = array();
        if ($files) {
            if (count($files) > 0) {
                $destinationPath = public_path() . '/uploads/temp';
                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0775, true);
                }
                foreach ($files as $item) {
                    $OriginalfileName = $item->getClientOriginalName();
                    $nameonly = preg_replace('/\..+$/', '', $OriginalfileName);
                    $fileExtension = $item->getClientOriginalExtension();
                    switch ($type) {
                        case 'file':
                            $fileName = $this->generateRandomString('5') . '--_--' . $nameonly . '.' . $fileExtension;
                            $moved = $item->move($destinationPath, $fileName);


                            $itemArray['fileName'][] = $fileName;
                            break;

                        case 'image':
                            $data = getimagesize($item);
                            $width = $data[0];
                            $height = $data[1];
                            if ($width < 720 || $height < 480) {
                                $status = FALSE;
                            } else {
                                $status = TRUE;
                            }
                            $fileName = $this->generateRandomString('5') . '--_--' . $nameonly . '.' . $fileExtension;
                            $item->move($destinationPath, $fileName);
                            $itemArray['fileName'][] = $fileName;
                            $itemArray['validate'][] = $status;
                            break;

                        default:
                            break;
                    }
                }
            }
        }
        return $itemArray;
    }
    public function uploadProcessProductImage($productsimages) {
        $productGallery = array();
        if ($productsimages) {
            if (count($productsimages) > 0) {
                $destinationPath = public_path() . '/uploads/temp';
                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0775, true);
                }
                foreach ($productsimages as $image) {
                    $OriginalfileName = $image->getClientOriginalName();
                    $nameonly = preg_replace('/\..+$/', '', $OriginalfileName);
                    $fileExtension = $image->getClientOriginalExtension();
                    $data = getimagesize($image);
                    $width = $data[0];
                    $height = $data[1];
                    if ($width < 720 || $height < 480) {
                        $status = FALSE;
                    } else {
                        $status = TRUE;
                    }
                    $fileName = $this->generateRandomString('5') . '--_--' . $nameonly . '.' . $fileExtension;
                    $image->move($destinationPath, $fileName);
                    $productGallery['fileName'][] = $fileName;
                    $productGallery['validate'][] = $status;
                }
            }
        }
        return $productGallery;
    }
    public function getAttachmentId($file, $type = 'digital', $remove = false, $crop = true) {

        if ($file) {

                $getItem = $file;
                $fileAttr = pathinfo($getItem);

                $nameItemOnly = $fileAttr['filename'];
                $fileExtension = $fileAttr['extension'];
                $attachments = new Attachments();
                $attachments->fileName = $nameItemOnly;
                $attachments->fileExtension = $fileExtension;
                $attachments->save();
                $attachmentId = $attachments->attachment_id;
                if ($attachmentId) {
                    $getRealPath = public_path() . '/uploads/temp/' . $file;
                    $fileName = $nameItemOnly . '_' . $attachmentId . '.' . $fileExtension;
                    switch ($type) {
                        case 'digital':
                            $destinationPath = public_path() . '/uploads/digital';
                            if (!File::exists($destinationPath)) {
                                File::makeDirectory($destinationPath, 0775, true);
                            }
                            $readFile = file_get_contents($getRealPath);
                            file_put_contents($destinationPath . '/' . $fileName, $readFile);

                            break;
                        case 'image':
                            $getDirectorywithSizes = $this->getAllAttachmentSizes();
                            foreach ($getDirectorywithSizes as $key => $value) {
                                $directoryName = $value['name'];
                                $width = $value['width'];
                                $height = $value['height'];
                                $destinationPath = public_path() . '/uploads/' . $directoryName;
                                if ($width == 0 && $height == 0) {
                                    $readImage = file_get_contents($getRealPath);
                                    file_put_contents($destinationPath . '/' . $fileName, $readImage);
                                } else {
                                    if ($crop) {

                                        $img = Image::make($getRealPath);
                                        $img->fit($width, $height, function ($constraint) {
                                            $constraint->upsize();
                                        });
                                        $img->save($destinationPath . '/' . $fileName);
                                    } else {

                                        $img = Image::make($getRealPath);
                                        $img->resize($width, null, function ($constraint) {
                                            $constraint->aspectRatio();
                                        })->save($destinationPath . '/' . $fileName);
                                    }
                                }
                            }
                            break;

                        default:
                            break;
                    }
                    if ($remove) {
                        unlink($getRealPath);
                    }

                    return $attachmentId;
                }
            }
        
    }
    /*
     * * Create Directories if Not Exist and Retrun Images Sizes
     */

    public function getAllAttachmentSizes() {

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

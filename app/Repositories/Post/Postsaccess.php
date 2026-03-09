<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Repositories\Post;

use File;
use Image;
use Request;
use App\BaseAccess;
use App\Models\Post;
use Stripe\EphemeralKey;
use App\Models\Attachments;
use App\Models\BlogCategory;
use App\Models\PostCategory;
use App\Models\AttachmentSizes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/**
 * Description of Postsaccess
 *
 * @author Noman
 */
class Postsaccess extends BaseAccess
{

    private $_post;
    private $_category;

    public function __construct()
    {
        $this->_post = new Post();
        $this->_category = new BlogCategory();
    }


    /*
     * * Create Post
     */
    public function savePost($data)
    {
        $this->_post->title = isset($data['title']) ? $data['title'] : "";
        if (isset($data['title'])) {
            $title_slug = str_slug($data['title'], '-');
            $this->_post->slug = $title_slug;
        }
        $this->_post->description = isset($data['description']) ? $data['description'] : "";
        $this->_post->blog_image = isset($data['blog_image_id']) ? $data['blog_image_id'] : "";
        $this->_post->save();

        $categories = isset($data['postCategory']) ? $data['postCategory'] : [];
        $this->_post->categories()->sync($categories); //  many-to-many relationship defined between Post and Category models

        return 'save';
    }

    /*
     * * Edit Update Post
     */
    public function updatePostData($postedData)
    {

        $postId = isset($postedData['editpost_id']) ? $postedData['editpost_id'] : "";
        $data = array();
        $data['title'] = isset($postedData['title']) ? $postedData['title'] : '';
        if (isset($data['title'])) {
            $title_slug = str_slug($postedData['title'], '-');
            $this->_post->slug = $title_slug;
        }
        $data['description'] = isset($postedData['description']) ? $postedData['description'] : '';

        if (isset($postedData['blog_image_id']) && $postedData['blog_image_id'] != "") {
            $data['blog_image'] =  $postedData['blog_image_id'];
        }

        $result = $this->_post->where('id', '=', $postId)->update($data);

        // Check if $postId is not empty before syncing the categories
        if (!empty($postId)) {
            $this->_post->find($postId)->categories()->sync($postedData['postCategory']);
        }


        if ($result > 0) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * * Delete Post By Id 
     */
    public function deletePost($postId)
    {

        if (!empty($postId)) {

            DB::table('post_category')->where('post_id', $postId)->delete();
            $result = Post::where('id', $postId)->delete();
        }
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * * Create blog category
     */
    public function saveBlogCategory($data)
    {
        $this->_category->category_name = isset($data['category_name']) ?  $data['category_name'] : "";
        if (isset($data['category_name']) && $data['category_name'] != '') {
            $category_slug = str_slug($data['category_name'], '-');
            $this->_category->category_slug = $category_slug;
        }
        $this->_category->description = isset($data['description']) ?  $data['description'] : "";
        $this->_category->parent_id = isset($data['parent_category']) ?  $data['parent_category'] : 0;
        $this->_category->save();
        return true;
    }

    /*
     * * Edit Update Category
     */
    public function updateCategoryData($postedData)
    {
        $categoryId = isset($postedData['editcategory_id']) ? $postedData['editcategory_id'] : "";
        $data = array();
        $data['category_name'] = isset($postedData['edit_category_name']) ? $postedData['edit_category_name'] : '';
        if (isset($postedData['edit_category_name'])) {
            $category_slug = str_slug($postedData['edit_category_name'], '-');
            $this->_category->slug = $category_slug;
        }
        $data['description'] = isset($postedData['description']) ? $postedData['description'] : '';

        if (isset($postedData['parent_id']) && $postedData['parent_id'] != "") {
            $data['parent_id'] =  $postedData['parent_id'];
        }

        $result = $this->_category->where('id', '=', $categoryId)->update($data);

        if ($result > 0) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * * Check if the Post Category name exist then return false 
     */
    public function getPostCategoryByname($data)
    {
        $nameCount = $this->_category->where('category_name', '=', $data['category_name'])->count();
        if ($nameCount > 0) {
            return false;
        } else {
            return true;
        }
    }

    /*
     * * Generate and return Random String 
     */
    public function generateRandomString($length)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxy';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /*
     * * Create Directories if Not Exist and Retrun Images upload 
     */
    public function getAttachmentId($file, $type, $remove = false, $crop = true)
    {

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
                                        if ($value['name'] == 'post') {
                                            $readImage = file_get_contents($getRealPath);
                                            file_put_contents($destinationPath . '/' . $fileName, $readImage);
                                        }
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


    public function objToArray($arr)
    {
        $arr = json_encode($arr);
        $res = json_decode($arr, true);
        return $res;
    }
}

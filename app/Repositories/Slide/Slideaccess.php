<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Repositories\Slide;

use App\BaseAccess;
use App\Http\Controllers\AttachmentController;
use App\Models\Slide;

/**
 * Description of Admin Access
 *
 * @author Asif
 */
class Slideaccess extends BaseAccess {

    private $slide_model_object;
    private $attachment_obj;

//
    public function __construct() {
        $this->slide_model_object = new Slide();
        $this->attachment_obj = new AttachmentController();
    }

    public function insertSlide(
        $slide_url,
        $Img_object,
        $cat,
        $status = 1,
        $bg_color,
        $slideSetId = null,
        $slide_type = null,
        $boxNumber = null
    ) {
        $attachmentId = '';
        if ($Img_object) {
            $fileName = $this->attachment_obj->uploadTempFiles($Img_object, 'image', '');
            $attachmentId = $this->attachment_obj->getAttachmentId($fileName['fileName'][0], 'image', true, false);
        }

        $slide = new Slide();
        $slide->attachment_id = $attachmentId;
        $slide->bg_color = $bg_color;
        $slide->link = $slide_url;
        $slide->category = $cat;
        $slide->status = $status;
        $slide->slide_set_id = $slideSetId;
        $slide->type = $slide_type;
        $slide->box_number = $boxNumber;
        $slide->save();
        return $slide;
    }

    public function updateSlide(
        $slide_id,
        $Img_object,
        $slide_url,
        $cat,
        $status,
        $bg_color,
        $setId = null,
        $slide_type = null
    ) {

        $slide = $this->slide_model_object->find($slide_id);

        if ($Img_object != '') {
            $fileName = $this->attachment_obj->uploadTempFiles($Img_object, 'image', '');
            $attachmentId = $this->attachment_obj->getAttachmentId($fileName['fileName'][0], 'image', true, false);

            $slide->attachment_id = $attachmentId;
        }


        $slide->link = $slide_url;
        $slide->category = $cat;
        $slide->status = $status;
        $slide->bg_color = $bg_color;
        $slide->slide_set_id = $setId;
        $slide->type = $slide_type ?? 'banner';
        $slide->save();
        return true;
    }

    public function deleteSlide($slide_id = '') {
        if ($slide_id == '') {
            return false;
        } else {
            $slide = $this->slide_model_object->find($slide_id);
            if ($slide == null) {
                return false;
            }
            $slide->delete();
            return true;
        }
    }

}

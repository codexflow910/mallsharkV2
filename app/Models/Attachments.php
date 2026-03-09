<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachments extends Model {

    protected $table = 'attachment';
    public $id;
    protected $primaryKey = 'attachment_id';
    public $size = 'media';

    public function getAttachmentUrlById() {
        $data = Attachments::find($this->id);
        // dd($data);
           if($data!=''){
               return $image_url = url('/uploads') . '/' . $this->size . '/' . $data->fileName . '_' . $this->id . '.' . $data->fileExtension;
            
           }
    
     
      }
      public function getAttachmentUrlByIdApi() {
        $data = Attachments::find($this->id);
           if($data!=''){
               return $image_url = url('/uploads') . '/' . $this->size . '/' . $data->fileName . '_' . $this->id . '.' . $data->fileExtension;
            
           }else{
            return false;
           }
    
     
      }
      public function getAttachmentUrlByIdEmailTempletes() {
        $data = Attachments::find($this->id);
     
           if($data!=''){
               return $image_url = ('/uploads') . '/' . $this->size . '/' . $data->fileName . '_' . $this->id . '.' . $data->fileExtension;
            
           }
    
     
      }
    public function getAttachmentUrlByIdAvatarApi() {
        $data = Attachments::find($this->id);
     
           if($data!=''){
               return $image_url = url('/uploads') . '/' . $this->size . '/' . $data->fileName. '_' . $this->id . '.' . $data->fileExtension;
            
           }
    
     
      }
    public function getAttachmentUrlByIdAvatar()
    {
        $data = Attachments::find($this->id);

        if ($data != '') {
            return $image_url = url('/uploads') . '/' . $this->size . '/' . $data->fileName . '_' . $this->id . '.' . $data->fileExtension;
        }
    }
    public function getAttachmentUrlById3() {
        $data = Attachments::find($this->id);
     
           if($data!=''){
               return $image_url = 'https://dev.ecommedge.com/uploads/'. $this->size . '/' . $data->fileName . '_' . $this->id . '.' . $data->fileExtension;
            
           }
    
     
      }
      public function getAttachmentUrlById2() {
        $data = Attachments::find($this->id);
     
           if($data!=''){
               return $image_url = url('/uploads') . '/' . $this->size . '/' . $data->fileName.'.' . $data->fileExtension;
            
           }
    
     
      }

}

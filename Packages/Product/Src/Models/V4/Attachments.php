<?php

namespace Packages\Product\Models\V4;

use Illuminate\Database\Eloquent\Model;
class Attachments extends Model {
    protected $table = 'attachment';
    public $id;
    protected $primaryKey = 'attachment_id';
    public $size = 'media';
    
    protected $fillable = [
        'fileName',
        'fileExtension',
        'alt',
    ];

    public function getAttachmentUrlById() {
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
    public function getAttachmentUrlByIdAvatar() {
        $data = Attachments::find($this->id);
     
           if($data!=''){
               return $image_url = url('/uploads') . '/' . $this->size . '/' . $data->fileName. '_' . $this->id . '.' . $data->fileExtension;
           }
      }

      public function getAttachmentUrlByIdAvatarApi() {
        $data = Attachments::find($this->id);
     
           if($data!=''){
               return $image_url = url('/uploads') . '/' . $this->size . '/' . $data->fileName. '_' . $this->id . '.' . $data->fileExtension;
            
           }
      }
      public function getDistributorLogo() {
        $data = Attachments::find($this->id);
           if($data!=''){
               return $image_url = url('/uploads') . '/' . $this->size . '/' . $data->fileName .'.' . $data->fileExtension;
            
           }
    
     
      }
}

<?php

namespace Packages\Distributors\Models\V2;

use Illuminate\Database\Eloquent\Model;

class Attachments extends Model
{

    protected $primaryKey = 'attachment_id';
    protected $table = 'attachment';

    public function getAttachmentUrlById()
    {
        $data = Attachments::find($this->id);
        if (!$data) {
            return false;
        }
        
        $folder = $this->size ?? 'distributors-documents';
        return url("/uploads/{$folder}/{$data->fileName}.{$data->fileExtension}");
    }

    public function getAttachmentUrlBySize()
    {
        $data = Attachments::find($this->id);
        if (!$data) {
            return false;
        }

        return url('/uploads') . '/' . $this->size . '/' . $data->fileName . '_' . $this->id . '.' . $data->fileExtension;
    }
}

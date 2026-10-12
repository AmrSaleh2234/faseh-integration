<?php

namespace App\Actions;

use App\Models\Attachment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class StoreAttachment
{
    public function store(Model $owner, UploadedFile $file, User $actor): Attachment
    {
        $path = $file->store('attachments/'.strtolower(class_basename($owner)).'/'.$owner->getKey(), 'local');
        return $owner->attachments()->create([
            'uploaded_by' => $actor->id,
            'disk' => 'local',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    }
}

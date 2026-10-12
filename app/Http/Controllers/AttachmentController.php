<?php

namespace App\Http\Controllers;

use App\Actions\DownloadAttachment;
use App\Models\Attachment;

class AttachmentController extends Controller
{
    public function download(Attachment $attachment, DownloadAttachment $action)
    {
        return $action->response($attachment);
    }
}

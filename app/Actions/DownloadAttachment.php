<?php

namespace App\Actions;

use App\Models\Attachment;
use App\Models\Invoice;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class DownloadAttachment
{
    public function response(Attachment $attachment)
    {
        Gate::authorize($attachment->attachable_type === Invoice::class ? 'invoices.view' : 'clients.view');
        abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404);
        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }
}

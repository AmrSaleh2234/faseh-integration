<?php

namespace App\Actions;

use App\Models\ActivityLog;
use App\Models\User;

class RecordActivity
{
    public function record(?User $actor, string $event, string $subject, ?int $id, string $target, string $summary = ''): void
    {
        ActivityLog::create(['actor_id' => $actor?->id, 'event' => $event, 'subject_type' => $subject, 'subject_id' => $id, 'target' => $target, 'summary' => $summary]);
    }
}

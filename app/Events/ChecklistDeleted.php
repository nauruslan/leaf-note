<?php

namespace App\Events;

use App\Models\Note;
use Illuminate\Foundation\Events\Dispatchable;

class ChecklistDeleted
{
    use Dispatchable;

    public function __construct(
        public Note $checklist,
        public int $userId
    ) {}
}

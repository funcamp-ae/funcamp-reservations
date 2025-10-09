<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TaskAttachment extends Model
{
    protected $fillable = ['task_id', 'type', 'path'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    // URL عام من تخزين hetzner
    public function getUrlAttribute(): string
    {
        return Storage::disk('hetzner')->url($this->path);
    }
}

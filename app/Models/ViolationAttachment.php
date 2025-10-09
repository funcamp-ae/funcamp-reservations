<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ViolationAttachment extends Model
{
    protected $fillable = ['violation_id', 'type', 'path'];

    public function violation()
    {
        return $this->belongsTo(Violation::class);
    }
}

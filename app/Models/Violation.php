<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Violation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'violation_type_id',
        'employee_id',
        'created_by',
        'violation_date',
        'violation_time',
        'violation_place',
        'photo_path', // قديم (اختياري يبقى)
        'employee_justification',
        'action_taken',
        'deduction_amount',
        'notes'
    ];

    protected $casts = [
        'violation_date' => 'date',
        'deduction_amount' => 'decimal:2'
    ];

    // علاقات
    public function type()     { return $this->belongsTo(ViolationType::class, 'violation_type_id'); }
    public function employee() { return $this->belongsTo(User::class, 'employee_id'); }
    public function creator()  { return $this->belongsTo(User::class, 'created_by'); }

    // مرفقات
    public function attachments()
    {
        return $this->hasMany(ViolationAttachment::class)->orderByDesc('id');
    }
    public function photos() { return $this->attachments()->where('type', 'photo'); }
    public function audios() { return $this->attachments()->where('type', 'audio'); }
    public function videos() { return $this->attachments()->where('type', 'video'); }

    protected $appends = ['photos_urls', 'audios_urls', 'videos_urls', 'photo_url'];

    public function getPhotosUrlsAttribute()
    {
        return $this->photos()->pluck('path')->map(fn($p) => Storage::disk('hetzner')->url($p))->values()->all();
    }
    public function getAudiosUrlsAttribute()
    {
        return $this->audios()->pluck('path')->map(fn($p) => Storage::disk('hetzner')->url($p))->values()->all();
    }
    public function getVideosUrlsAttribute()
    {
        return $this->videos()->pluck('path')->map(fn($p) => Storage::disk('hetzner')->url($p))->values()->all();
    }

    // للتوافق الخلفي مع الصفحات القديمة
    public function getPhotoUrlAttribute()
    {
        $first = $this->photos()->value('path');
        if ($first) return Storage::disk('hetzner')->url($first);
        return $this->photo_path ? Storage::disk('hetzner')->url($this->photo_path) : null;
    }

    // فلاتر قديمة كما هي
    public function scopeFilter($query, $filters)
    {
        return $query
            ->when($filters['employee_id'] ?? null, fn($q, $v) => $q->where('employee_id', $v))
            ->when($filters['violation_type_id'] ?? null, fn($q, $v) => $q->where('violation_type_id', $v))
            ->when($filters['action_taken'] ?? null, fn($q, $v) => $q->where('action_taken', $v));
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappPlaceholder extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_placeholders';

    protected $fillable = [
        'placeholder', // e.g. "[Camp Address]"
        'variable_key', // e.g. "service_location"
    ];
}

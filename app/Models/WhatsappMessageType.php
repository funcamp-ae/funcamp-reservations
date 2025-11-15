<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappMessageType extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_message_types';

    protected $fillable = [
        'key',
    ];
    
}

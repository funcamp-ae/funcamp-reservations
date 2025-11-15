<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_message_types', function (Blueprint $table) {
            $table->id();
            $table->string('key', 191)->unique();
            $table->timestamps();
        });

        // Seed default known types
        $defaultTypes = [
            'show_price',
            'invoice',
            'receipt',
            'reservation_data',
            'evaluation',
            'payment_link_created',
            'payment_link_resend',
            'booking_reminder',
            'booking_ending_reminder',
            'manual_template',
            'reservation_confirmation',
            
        ];

        foreach ($defaultTypes as $type) {
            DB::table('whatsapp_message_types')->insert([
                'key' => $type,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_message_types');
    }
};

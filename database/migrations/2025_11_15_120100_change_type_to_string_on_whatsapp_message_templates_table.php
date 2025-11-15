<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change enum to string (VARCHAR)
        DB::statement("ALTER TABLE whatsapp_message_templates MODIFY COLUMN type VARCHAR(191) NOT NULL");
    }

    public function down(): void
    {
        // Revert back to the previous ENUM set used in the project
        DB::statement("ALTER TABLE whatsapp_message_templates MODIFY COLUMN type ENUM(
            'show_price',
            'invoice', 
            'receipt',
            'reservation_data',
            'evaluation',
            'payment_link_created',
            'payment_link_resend',
            'booking_reminder',
            'booking_ending_reminder',
            'manual_template'
        ) NOT NULL");
    }
};

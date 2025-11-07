<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add 'reservation_confirmation' to the type enum
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
            'manual_template',
            'reservation_confirmation'
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert to previous template types (remove 'reservation_confirmation')
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'voice_note',
                'video_note',
                'video_note_direct_key',
                'image_before_receiving',
                'image_after_delivery',
                'voice_note_logout',
                'video_note_logout',

            ]);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('voice_note')->nullable();
            $table->string('video_note')->nullable();
            $table->string('video_note_direct_key', 191)->nullable();
            $table->string('image_before_receiving')->nullable();
            $table->string('image_after_delivery')->nullable();
            $table->string('voice_note_logout')->nullable();
            $table->string('video_note_logout')->nullable();

        });
    }
};

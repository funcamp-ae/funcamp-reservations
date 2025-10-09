<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('camp_report_items', function (Blueprint $table) {
            if (Schema::hasColumn('camp_report_items', 'photo_path')) {
                $table->dropColumn('photo_path');
            }
            if (Schema::hasColumn('camp_report_items', 'audio_path')) {
                $table->dropColumn('audio_path');
            }
            if (Schema::hasColumn('camp_report_items', 'video_path')) {
                $table->dropColumn('video_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('camp_report_items', function (Blueprint $table) {
            $table->string('photo_path')->nullable();
            $table->string('audio_path')->nullable();
            $table->string('video_path')->nullable();
        });
    }
};

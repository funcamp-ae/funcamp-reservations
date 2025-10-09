<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            if (Schema::hasColumn('daily_reports', 'audio_attachment')) {
                $table->dropColumn('audio_attachment');
            }
            if (Schema::hasColumn('daily_reports', 'video_attachment')) {
                $table->dropColumn('video_attachment');
            }
            if (Schema::hasColumn('daily_reports', 'photo_attachment')) {
                $table->dropColumn('photo_attachment');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->string('audio_attachment')->nullable();
            $table->string('video_attachment')->nullable();
            $table->string('photo_attachment')->nullable();
        });
    }
};

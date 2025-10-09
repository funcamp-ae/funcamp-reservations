<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_report_media', function (Blueprint $table) {
            $table->id();

            // العمود الرابط مع daily_reports
            $table->unsignedBigInteger('daily_report_id');

            // نوع الميديا
            $table->enum('type', ['photo', 'video', 'audio']);

            // مسار الملف
            $table->string('path');

            $table->timestamps();

            // مفتاح أجنبي يربط الميديا بالتقرير
            $table->foreign('daily_report_id')
                  ->references('id')
                  ->on('daily_reports')
                  ->onDelete('cascade'); // لو تحذف التقرير تحذف معاه الميديا
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_report_media');
    }
};

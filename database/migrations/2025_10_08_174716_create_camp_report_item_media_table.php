<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('camp_report_item_media', function (Blueprint $table) {
            $table->id();

            // المفتاح الأجنبي مع camp_report_items
            $table->unsignedBigInteger('camp_report_item_id');

            $table->enum('type', ['photo', 'video', 'audio']);
            $table->string('path');
            $table->timestamps();

            // Foreign Key
            $table->foreign('camp_report_item_id')
                  ->references('id')
                  ->on('camp_report_items')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('camp_report_item_media');
    }
};

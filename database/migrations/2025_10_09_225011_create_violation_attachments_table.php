<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('violation_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('violation_id')->constrained('violations')->cascadeOnDelete();
            $table->enum('type', ['photo', 'audio', 'video']);
            $table->string('path', 1024); // مسار الملف على hetzner (أو key FilePond للفيديو)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violation_attachments');
    }
};

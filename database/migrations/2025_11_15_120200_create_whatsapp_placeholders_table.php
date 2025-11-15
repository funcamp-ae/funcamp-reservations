<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_placeholders', function (Blueprint $table) {
            $table->id();
            $table->string('placeholder', 191)->unique(); // e.g. [Camp Address]
            $table->string('variable_key', 191); // e.g. service_location
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_placeholders');
    }
};

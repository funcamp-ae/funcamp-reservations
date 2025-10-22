<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Modify Google columns to handle longer tokens
            $table->text('google_token')->nullable()->change();
            $table->text('google_refresh_token')->nullable()->change();
            $table->string('google_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert back to string (this might cause data loss if tokens are too long)
            $table->string('google_token')->nullable()->change();
            $table->string('google_refresh_token')->nullable()->change();
        });
    }
};

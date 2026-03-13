<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('camp_report_items', function (Blueprint $table) {
            $table->string('item_status')->nullable()->after('notes');
            $table->string('item_status_other')->nullable()->after('item_status');
            $table->unsignedInteger('damaged_count')->nullable()->after('item_status_other');
        });
    }

    public function down(): void
    {
        Schema::table('camp_report_items', function (Blueprint $table) {
            $table->dropColumn(['item_status', 'item_status_other', 'damaged_count']);
        });
    }
};
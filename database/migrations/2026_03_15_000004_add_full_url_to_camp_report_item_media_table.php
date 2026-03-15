<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('camp_report_item_media', function (Blueprint $table) {
            if (! Schema::hasColumn('camp_report_item_media', 'full_url')) {
                $table->text('full_url')->nullable()->after('path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('camp_report_item_media', function (Blueprint $table) {
            if (Schema::hasColumn('camp_report_item_media', 'full_url')) {
                $table->dropColumn('full_url');
            }
        });
    }
};

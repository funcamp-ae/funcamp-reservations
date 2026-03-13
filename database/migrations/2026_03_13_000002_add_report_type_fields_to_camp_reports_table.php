<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('camp_reports', function (Blueprint $table) {
            $table->string('report_type')->nullable()->after('report_date');
            $table->string('custom_report_type')->nullable()->after('service_id');
        });
    }

    public function down(): void
    {
        Schema::table('camp_reports', function (Blueprint $table) {
            $table->dropColumn(['report_type', 'custom_report_type']);
        });
    }
};
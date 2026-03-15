<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('camp_report_summary_exports', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('rows');
            $table->text('error_message')->nullable()->after('status');
            $table->timestamp('started_at')->nullable()->after('prepared_at');
            $table->timestamp('finished_at')->nullable()->after('started_at');
            $table->index(['user_id', 'token', 'status'], 'camp_summary_exports_user_token_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('camp_report_summary_exports', function (Blueprint $table) {
            $table->dropIndex('camp_summary_exports_user_token_status_idx');
            $table->dropColumn(['status', 'error_message', 'started_at', 'finished_at']);
        });
    }
};

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
        if (Schema::hasTable('equipment_location_exports')) {
            Schema::table('equipment_location_exports', function (Blueprint $table) {
                if (! Schema::hasColumn('equipment_location_exports', 'notify_whatsapp')) {
                    $table->boolean('notify_whatsapp')->default(false)->after('user_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('equipment_location_exports')) {
            Schema::table('equipment_location_exports', function (Blueprint $table) {
                if (Schema::hasColumn('equipment_location_exports', 'notify_whatsapp')) {
                    $table->dropColumn('notify_whatsapp');
                }
            });
        }
    }
};

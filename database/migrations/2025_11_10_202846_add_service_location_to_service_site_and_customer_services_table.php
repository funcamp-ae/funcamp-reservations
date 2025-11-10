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
        Schema::table('service_site_and_customer_services', function (Blueprint $table) {
            $table->text('service_location')->nullable()->after('service_site');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_site_and_customer_services', function (Blueprint $table) {
            $table->dropColumn('service_location');
        });
    }
};

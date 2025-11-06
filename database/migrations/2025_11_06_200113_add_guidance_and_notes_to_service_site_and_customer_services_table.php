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
            $table->json('guidance_images')->nullable()->after('workerphone'); // Multiple images
            $table->json('guidance_videos')->nullable()->after('guidance_images'); // Multiple video links
            $table->longText('important_notes')->nullable()->after('guidance_videos'); // Rich text editor
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
            $table->dropColumn(['guidance_images', 'guidance_videos', 'important_notes']);
        });
    }
};

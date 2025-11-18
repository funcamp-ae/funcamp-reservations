<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->where('id', 1)->update(['value' => 'Funcamp']);
        DB::table('settings')->where('id', 2)->update(['value' => 'Funcamp']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Optionally, you can revert the changes here if needed
        DB::table('settings')->where('id', 1)->update(['value' => null]);
        DB::table('settings')->where('id', 2)->update(['value' => null]);
    }
};
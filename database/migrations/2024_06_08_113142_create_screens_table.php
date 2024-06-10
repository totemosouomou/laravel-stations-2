<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScreensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('screens', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->foreignId('screen_id')->constrained()->onDelete('cascade');
        });

        Schema::table('sheets', function (Blueprint $table) {
            $table->foreignId('screen_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['screen_id']);
            $table->dropColumn('screen_id');
        });

        Schema::table('sheets', function (Blueprint $table) {
            $table->dropForeign(['screen_id']);
            $table->dropColumn('screen_id');
        });

        Schema::dropIfExists('screens');
    }
}

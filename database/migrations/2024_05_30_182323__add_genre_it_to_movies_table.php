<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGenreItToMoviesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movies', function (Blueprint $table) {
            //
            $table->foreignId('genre_id')->nullable()->default(null)->constrained(); // ジャンルID（外部キー）を追加し、NULLをデフォルト値として設定
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('movies', function (Blueprint $table) {
            //
            $table->dropForeign(['genre_id']); // 外部キー制約を削除
            $table->dropColumn('genre_id'); // カラムを削除
        });
    }
}

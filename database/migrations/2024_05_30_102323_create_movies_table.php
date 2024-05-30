<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoviesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id(); // ID
            $table->text('title'); // 映画タイトル
            $table->text('image_url'); // 画像URL
            $table->integer('published_year')->nullable(); // 公開年
            $table->tinyInteger('is_showing')->default(false); // 上映中かどうか
            $table->text('description')->nullable(); // 概要
            $table->timestamps(); // 登録日時と更新日時のカラム
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movies');
    }
}

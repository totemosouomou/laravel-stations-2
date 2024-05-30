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
            $table->string('title', 255)->unique(); // 映画タイトル（ユニーク制約）
            $table->text('image_url'); // 画像URL
            $table->integer('published_year'); // 公開年
            $table->tinyInteger('is_showing')->default(false); // 上映中かどうか
            $table->text('description'); // 概要
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

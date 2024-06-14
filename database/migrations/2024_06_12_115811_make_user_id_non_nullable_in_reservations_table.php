<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Reservation;

class MakeUserIdNonNullableInReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // PDOを使用してデータベースに接続
        $pdo = DB::connection()->getPdo();

        // まず、NULLのuser_idを持つレコードにデフォルト値を設定します
        $statement = $pdo->prepare("UPDATE reservations SET user_id = 0 WHERE user_id IS NULL");
        $statement->execute();

        // user_id カラムを非NULLに変更します
        Schema::table('reservations', function (Blueprint $table) use ($pdo) {
            $statement = $pdo->prepare("ALTER TABLE reservations MODIFY COLUMN user_id BIGINT UNSIGNED NOT NULL DEFAULT 0");
            $statement->execute();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // PDOを使用してデータベースに接続
        $pdo = DB::connection()->getPdo();

        // user_id カラムを NULL 許容に戻します
        Schema::table('reservations', function (Blueprint $table) use ($pdo) {
            $statement = $pdo->prepare("ALTER TABLE reservations MODIFY COLUMN user_id BIGINT UNSIGNED NULL");
            $statement->execute();
        });
    }
}

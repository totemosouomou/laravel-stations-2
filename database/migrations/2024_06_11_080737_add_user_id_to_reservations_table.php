<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;
use App\Models\Reservation;
use App\Models\User;

class AddUserIdToReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade')->after('id');
        });

        // すでに存在する reservations レコードに user_id を割り当てる
        $reservations = Reservation::whereNull('user_id')->select('id', 'name', 'email')->get();
        $users = User::select('email', 'id')->get()->keyBy('email');

        $createData = [];
        $updateData = [];

        foreach ($reservations as $reservation) {
            if ($users->has($reservation->email)) {
                $updateData[] = [
                    'id' => $reservation->id,
                    'user_id' => $users[$reservation->email]->id,
                ];
            } else {
                $createData[] = [
                    'name' => $reservation->name,
                    'email' => $reservation->email,
                    'password' => Hash::make('password'),
                ];
            }
        }

        // 新しいユーザーをバルク作成
        if (!empty($createData)) {
            User::insert($createData);
        }

        // 作成したユーザーのIDを再度取得して更新データに追加
        $users = User::select('email', 'id')->get()->keyBy('email');
        foreach ($reservations as $reservation) {
            if ($users->has($reservation->email)) {
                $updateData[] = [
                    'id' => $reservation->id,
                    'user_id' => $users[$reservation->email]->id,
                ];
            }
        }

        $updateData = array_unique($updateData, SORT_REGULAR);

        // バルク更新
        DB::transaction(function () use ($updateData) {
            foreach ($updateData as $data) {
                Reservation::where('id', $data['id'])->update(['user_id' => $data['user_id']]);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // すでに存在する reservations レコードの user_id を削除する
        // Reservation::query()->update(['user_id' => null]);

        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
}

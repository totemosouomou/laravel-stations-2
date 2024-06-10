<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Screen;

class ScreenTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('screens')->delete();
        $id = 1;

        $screenNames = ['A', 'B', 'C'];

        foreach ($screenNames as $name) {
            Screen::create([
                'id' => $id++,
                'name' => $name
            ]);
        }
    }
}

    // ・ABCのいずれの館でも映画を上映できる。
    // ・上映を終えた（空きの出た）館はすぐ次の上映スケジュールを入れる。
    // ・上映時間は120分で終了
    // ・前の上映終了後5分から15分後に次の上映を開始（ランダム）
    // ・本日の現時刻以降のスケジュールを追加
    // ・最初の上映は現時刻から5分後に開始
    // ・上記のスケジュールでランダムな作品を上映する。
    // ・各スクリーンに最初のスケジュールを割り当て、終了時間と映画IDをメモリに保持。
    // ・do-while ループを使用して、同時刻に同映画 ID が重複しないように選択します。
    // ・usedMovieIds 配列を使用して、使用済みの映画 ID を追跡します。
    // ・recentMovieIds 配列を使用して、直近に上映された映画 ID を追跡し、それらと重複しないように選択します。
    // ・スケジュールの開始時間が22:00を超えないように調整。

// screensテーブルの生成をしsheetsテーブルと紐づけ、ScreenテーブルのレコードABCごとにsheetsテーブルのレコード（1から15）の席を割り当てる。
// 今までは1館（15席）でしたのでscheduleにsheetsテーブルを紐づけるだけでしたが、これからは3館（15席かける3館）になります。
// schedulesは今まで通りmovieと紐づいていて、movieに対してschedulesのレコードが増えることになります。
// 管理者がscheduleを登録する際、screenがABCどれになるかは空きがあるscreenに登録できます。
// ユーザーがscheduleを予約する際、screenがABCどれになるかは表示せず自動的に決まる仕組みにします。
// よって、改修するのは、管理者がscheduleを登録する際、sheetsだけでなくsheetsとscreensを見ながら登録できる方法の部分が主になります。
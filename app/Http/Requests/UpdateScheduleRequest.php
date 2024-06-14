<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'start_time_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:end_time_date'],
            'start_time_time' => ['required', 'date_format:H:i'],
            'end_time_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_time_date'],
            'end_time_time' => ['required', 'date_format:H:i'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'movie_id.required' => '映画IDを指定してください。',
            'movie_id.exists' => '指定された映画IDは存在しません。',
            'screen_id.required' => 'スクリーンIDを指定してください。',
            'screen_id.exists' => '指定されたスクリーンIDは存在しません。',
            'start_time_date.required' => '開始日を指定してください。',
            'start_time_date.date_format' => '開始日の形式が正しくありません。',
            'start_time_date.before_or_equal' => '開始日は終了日以前の日付を指定してください。',
            'start_time_time.required' => '開始時間を指定してください。',
            'start_time_time.date_format' => '開始時間の形式が正しくありません。',
            'end_time_date.required' => '終了日を指定してください。',
            'end_time_date.date_format' => '終了日の形式が正しくありません。',
            'end_time_date.after_or_equal' => '終了日は開始日以降の日付を指定してください。',
            'end_time_time.required' => '終了時間を指定してください。',
            'end_time_time.date_format' => '終了時間の形式が正しくありません。',
        ];
    }
}

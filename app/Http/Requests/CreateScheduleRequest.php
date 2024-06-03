<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateScheduleRequest extends FormRequest
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
            'movie_id' => ['required'],
            'start_time_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:end_time_date'],
            'start_time_time' => ['required', 'date_format:H:i'],
            'end_time_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_time_date'],
            'end_time_time' => ['required', 'date_format:H:i'],
        ];
    }
}

// namespace App\Http\Requests;

// use Illuminate\Foundation\Http\FormRequest;
// use Carbon\Carbon;

// class UpdateScheduleRequest extends FormRequest
// {
//     /**
//      * Determine if the user is authorized to make this request.
//      *
//      * @return bool
//      */
//     public function authorize()
//     {
//         return true;
//     }

//     /**
//      * Get the validation rules that apply to the request.
//      *
//      * @return array
//      */
//     public function rules()
//     {
//         return [
//             'movie_id' => 'required|exists:movies,id',
//             'start_time_date' => 'required|date_format:Y-m-d|before_or_equal:end_time_date',
//             'start_time_time' => 'required|date_format:H:i',
//             'end_time_date' => 'required|date_format:Y-m-d|after_or_equal:start_time_date',
//             'end_time_time' => 'required|date_format:H:i',
//         ];
//     }

//     /**
//      * Get the custom messages for validator errors.
//      *
//      * @return array
//      */
//     public function messages()
//     {
//         return [
//             'movie_id.required' => '映画IDを指定してください。',
//             'movie_id.exists' => '指定された映画IDは存在しません。',
//             'start_time_date.required' => '開始日を指定してください。',
//             'start_time_date.date_format' => '開始日の形式が正しくありません。',
//             'start_time_date.before_or_equal' => '開始日は終了日以前の日付を指定してください。',
//             'start_time_time.required' => '開始時間を指定してください。',
//             'start_time_time.date_format' => '開始時間の形式が正しくありません。',
//             'end_time_date.required' => '終了日を指定してください。',
//             'end_time_date.date_format' => '終了日の形式が正しくありません。',
//             'end_time_date.after_or_equal' => '終了日は開始日以降の日付を指定してください。',
//             'end_time_time.required' => '終了時間を指定してください。',
//             'end_time_time.date_format' => '終了時間の形式が正しくありません。',
//         ];
//     }

//     /**
//      * Add additional validation rules.
//      */
//     protected function prepareForValidation()
//     {
//         $this->merge([
//             'start_time' => "{$this->start_time_date} {$this->start_time_time}",
//             'end_time' => "{$this->end_time_date} {$this->end_time_time}",
//         ]);
//     }

//     /**
//      * Configure the validator instance.
//      *
//      * @param  \Illuminate\Validation\Validator  $validator
//      * @return void
//      */
//     public function withValidator($validator)
//     {
//         $validator->after(function ($validator) {
//             $startTime = new Carbon($this->start_time);
//             $endTime = new Carbon($this->end_time);
//             $differenceInMinutes = $endTime->diffInMinutes($startTime);

//             if ($startTime->eq($endTime)) {
//                 $validator->errors()->add('start_time_time', '開始時間と終了時間が同一です。');
//                 $validator->errors()->add('end_time_time', '開始時間と終了時間が同一です。');
//             }

//             if ($startTime->gte($endTime)) {
//                 $validator->errors()->add('start_time_time', '開始時間は終了時間より前に設定してください。');
//                 $validator->errors()->add('end_time_time', '開始時間は終了時間より前に設定してください。');
//             }

//             if ($differenceInMinutes < 6) {
//                 $validator->errors()->add('start_time_time', '開始時間と終了時間の間に5分以上の間隔を空けてください。');
//                 $validator->errors()->add('end_time_time', '開始時間と終了時間の間に5分以上の間隔を空けてください。');
//             }
//         });
//     }
// }


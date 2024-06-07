<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateReservationRequest extends FormRequest
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
            'schedule_id' => ['required'],
            'sheet_id' => ['required', 'integer', 'between:1,15'],
            'name' => ['required'],
            'email' => ['required', 'email:strict,dns'],
            'date' => ['required', 'date_format:Y-m-d']
        ];
    }
}

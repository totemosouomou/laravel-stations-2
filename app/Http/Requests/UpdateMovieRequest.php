<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMovieRequest extends FormRequest
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
        $id = $this->route('id');

        return [
            'title' => ['required', 'string', 'unique:movies,title,' . $id,],
            'image_url' => ['required', 'url'],
            'published_year' => ['required', 'integer', 'between:2000,2024'],
            'description' => ['required', 'string'],
            'is_showing' => ['required', 'boolean'],
            'genre' => ['required', 'string'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostBookRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'isbn'=>'required|string|min:13|max:13|unique:books',
            'title'=>'required|string|min:1',
            'description'=>'required|string|min:1',
            'authors'=>'required|array|min:1',
            'authors.*'=>'required|integer|exists:authors,id',
        ];
    }
}

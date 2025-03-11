<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Allow all authenticated users
    }

    public function rules()
    {
        return [
            // 'name' => 'required|string|max:255|unique:categories,name,' . $this->route('id'),
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Allow all authenticated users
    }

    public function rules()
    {
        return [
            // 'product_name' => 'required|string|max:255',
            // 'product_detail' => 'required|string',
            // 'priceUSD' => 'required|numeric|min:0',
            // 'category_id' => 'required|exists:categories,id',
            // 'seller_id' => 'required|exists:users,id',
            // 'image' => 'nullable|image|mimes:jpg,png,jpeg,gif|max:2048'
        ];
    }
}

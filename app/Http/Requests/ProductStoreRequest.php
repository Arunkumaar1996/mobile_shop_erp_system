<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('create-products');
    }

    public function rules(): array
    {
        return [
            'brand_id' => ['required', 'exists:brands,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'exists:sub_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'model_no' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_imei_tracked' => ['boolean'],
            'status' => ['boolean'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            
            // Variants validation
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.color_id' => ['nullable', 'exists:colors,id'],
            'variants.*.storage_variant_id' => ['nullable', 'exists:storage_variants,id'],
            'variants.*.ram_variant_id' => ['nullable', 'exists:ram_variants,id'],
            'variants.*.sku' => ['required', 'string', 'unique:product_variants,sku'],
            'variants.*.cost_price' => ['required', 'numeric', 'min:0'],
            'variants.*.selling_price' => ['required', 'numeric', 'min:0'],
            'variants.*.alert_quantity' => ['required', 'integer', 'min:0'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermission('edit-products');
    }

    public function rules(): array
    {
        $product = $this->route('product');
        $productId = is_object($product) ? $product->id : $product;

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
            'delete_gallery_ids' => ['nullable', 'array'],
            'delete_gallery_ids.*' => ['integer', 'exists:product_images,id'],
            
            // Variants validation
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id' => ['nullable', 'integer'], // Null for new variants added during edit
            'variants.*.color_id' => ['nullable', 'exists:colors,id'],
            'variants.*.storage_variant_id' => ['nullable', 'exists:storage_variants,id'],
            'variants.*.ram_variant_id' => ['nullable', 'exists:ram_variants,id'],
            'variants.*.sku' => [
                'required', 'string',
                function ($attribute, $value, $fail) use ($productId) {
                    preg_match('/variants\.(\d+)\.sku/', $attribute, $matches);
                    $index = $matches[1] ?? null;
                    if ($index !== null) {
                        $variantId = request()->input("variants.{$index}.id");
                        $query = \DB::table('product_variants')->where('sku', $value);
                        if ($variantId) {
                            $query->where('id', '!=', $variantId);
                        }
                        if ($query->exists()) {
                            $fail('The SKU "' . $value . '" has already been taken by another product.');
                        }
                    }
                }
            ],
            'variants.*.cost_price' => ['required', 'numeric', 'min:0'],
            'variants.*.selling_price' => ['required', 'numeric', 'min:0'],
            'variants.*.alert_quantity' => ['required', 'integer', 'min:0'],
        ];
    }
}

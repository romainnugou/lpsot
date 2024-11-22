<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_name' => 'required|string|max:255',
            'need_by' => 'required|date|after_or_equal:today',
            'order_items' => 'required|array|min:1',
            'order_items.*.product_id' => 'required|exists:products,id',
            'order_items.*.quantity' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'need_by.after_or_equal' => 'The need by date must be today or after.',
            'order_items.required' => 'Minimum one order item is required.',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'order_items' => $this->order_items ?? [],
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $productIds = array_column($this->input('order_items'), 'product_id');
            $productTypes = Product::whereIn('id', $productIds)->pluck('product_type_id')->unique();

            if ($productTypes->count() > 1) {
                $validator->errors()->add(
                    'order_items',
                    'Every product you chose for this order must be of the same type.'
                );
            }
        });
    }
}

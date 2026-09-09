<?php

namespace App\Http\Requests;

use App\Models\ProductColor;
use App\Models\ProductSize;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_name' => 'required|string',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string',
            'delivery_method' => 'required|in:takeaway,delivery',
            'delivery_address' => 'nullable|string',
            'district_id' => 'nullable|exists:districts,id',
            'delivery_date' => 'nullable|date|after_or_equal:today',
            'with_card' => 'nullable|boolean',
            'recipient_name' => 'nullable|string',
            'card_message' => 'nullable|string',
            'payment_method' => 'required|in:online,on_site,card',
            'promo_code' => 'nullable|string',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.size_id' => 'nullable|integer|exists:sizes,id',
            'items.*.color_id' => 'nullable|integer|exists:product_colors,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }

    /**
     * Add rules that depend on other fields already being present.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->sometimes(
            ['delivery_address', 'district_id', 'delivery_date'],
            'required',
            fn ($input) => $input->delivery_method === 'delivery'
        );

        $validator->after(function ($validator) {
            foreach ($this->input('items', []) as $index => $item) {
                if (empty($item['product_id'])) {
                    continue;
                }

                if (! empty($item['size_id']) && ! ProductSize::where('product_id', $item['product_id'])->where('size_id', $item['size_id'])->where('is_active', true)->exists()) {
                    $validator->errors()->add("items.$index.size_id", 'The selected size is not available for this product.');
                }

                if (! empty($item['color_id']) && ! ProductColor::where('id', $item['color_id'])->where('product_id', $item['product_id'])->where('is_active', true)->exists()) {
                    $validator->errors()->add("items.$index.color_id", 'The selected color is not available for this product.');
                }
            }
        });
    }
}

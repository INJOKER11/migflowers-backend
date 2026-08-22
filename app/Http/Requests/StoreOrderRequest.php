<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }

    /**
     * Add rules that depend on other fields already being present.
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->sometimes(
            ['delivery_address', 'district_id', 'delivery_date'],
            'required',
            fn ($input) => $input->delivery_method === 'delivery'
        );
    }
}

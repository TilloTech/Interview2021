<?php

declare(strict_types=1);

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
            'shipping_name' => 'required|string|max:255',
            'shipping_email' => 'required|email|max:255',
            'shipping_phone' => 'nullable|string|max:20',
            'shipping_address' => 'required|string',
            'shipping_address2' => 'nullable|string',
            'shipping_city' => 'required|string|max:255',
            'shipping_postcode' => 'required|string|max:20',
            'shipping_country' => 'required|string|max:255',
            'card_number' => 'required|string|regex:/^\d{16}$/',
            'expiry_date' => 'required|string|regex:/^\d{2}\/\d{2}$/',
            'cvv' => 'required|string|regex:/^\d{3,4}$/',
            'cart_items' => 'required|array|min:1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'shipping_name.required' => 'Shipping name is required.',
            'shipping_email.required' => 'Shipping email is required.',
            'shipping_email.email' => 'Please enter a valid email address.',
            'shipping_address.required' => 'Shipping address is required.',
            'shipping_city.required' => 'Shipping city is required.',
            'shipping_postcode.required' => 'Shipping postcode is required.',
            'shipping_country.required' => 'Shipping country is required.',
            'card_number.regex' => 'Card number must be exactly 16 digits',
            'expiry_date.regex' => 'Expiry date must be in format: MM/YY',
            'cvv.regex' => 'CVV must be 3 or 4 digits',
            'cart_items.min' => 'Your cart must contain at least one item.',
        ];
    }
}

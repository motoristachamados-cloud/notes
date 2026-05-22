<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class PaymentWebhookRequest extends FormRequest
{
    /**
     * Autoriza webhook Mercado Pago.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras validação webhook.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Mercado Pago payload
            |--------------------------------------------------------------------------
            |
            | {
            |   "data": {
            |     "id": "123456"
            |   }
            | }
            |
            */

            'data.id' => [
                'required',
                'string',
                'max:120',
            ],

            'type' => [
                'nullable',
                'string',
                'max:120',
            ],

            'action' => [
                'nullable',
                'string',
                'max:120',
            ],
        ];
    }
}
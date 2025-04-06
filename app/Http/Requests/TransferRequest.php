<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'value' => 'required|numeric|min:1',
            'payer' => 'required|exists:users,id',
            'payee' => 'required|exists:users,id|different:payer',
        ];
    }
}

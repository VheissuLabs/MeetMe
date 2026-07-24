<?php

namespace App\Http\Requests\Settings;

use App\Concerns\PasswordValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileDeleteRequest extends FormRequest
{
    use PasswordValidationRules;

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        // OAuth-only accounts never set a password, so there's nothing to
        // confirm against — the delete dialog is the confirmation.
        if ($this->user()->password === null) {
            return [];
        }

        return [
            'password' => $this->currentPasswordRules(),
        ];
    }
}

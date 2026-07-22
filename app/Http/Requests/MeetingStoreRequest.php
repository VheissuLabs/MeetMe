<?php

namespace App\Http\Requests;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MeetingStoreRequest extends FormRequest
{
    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'qr_token' => [
                'required',
                'string',
                'ulid',
                'exists:users,qr_token',
                function (string $attribute, mixed $value, Closure $fail) {
                    if ($this->user()->qr_token === $value) {
                        $fail(__("That's your own code — find someone new to meet!"));
                    }
                },
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'qr_token.exists' => __("That code doesn't belong to anyone here."),
            'qr_token.ulid' => __("That doesn't look like a MeetMe code."),
        ];
    }

    public function recipient(): User
    {
        return User::query()->where('qr_token', $this->validated('qr_token'))->sole();
    }
}

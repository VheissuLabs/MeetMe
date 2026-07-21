<?php

namespace App\Http\Requests\Settings;

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    use ProfileValidationRules;

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            ...$this->profileRules($this->user()->id),
            'pronouns' => ['nullable', 'string', 'max:30'],
            'x_username' => ['nullable', 'string', 'regex:/^[A-Za-z0-9_]{1,15}$/'],
            'bluesky_handle' => ['nullable', 'string', 'max:253', 'regex:/^([a-z0-9]([a-z0-9-]*[a-z0-9])?\.)+[a-z]{2,}$/i'],
            'email_visible' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'x_username.regex' => __('X usernames are 1-15 letters, numbers, or underscores.'),
            'bluesky_handle.regex' => __('Bluesky handles look like name.bsky.social or your own domain.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'x_username' => blank($this->x_username) ? null : ltrim((string) $this->x_username, '@'),
            'bluesky_handle' => blank($this->bluesky_handle) ? null : ltrim(strtolower((string) $this->bluesky_handle), '@'),
            'pronouns' => blank($this->pronouns) ? null : $this->pronouns,
            'email_visible' => $this->boolean('email_visible'),
        ]);
    }
}

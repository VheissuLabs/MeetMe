<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MeetingAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('answer', $this->route('meeting'));
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'answer' => ['required', 'string', 'max:2000'],
        ];
    }
}

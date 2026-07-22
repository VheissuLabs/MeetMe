<?php

namespace App\Http\Requests;

use App\Enums\MeetingStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class MeetingResolveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('resolve', $this->route('meeting'));
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(MeetingStatus::class), Rule::in([MeetingStatus::Confirmed->value, MeetingStatus::Rejected->value])],
            'rating' => [
                Rule::requiredIf($this->input('status') === MeetingStatus::Confirmed->value),
                Rule::prohibitedIf($this->input('status') === MeetingStatus::Rejected->value),
                'integer',
                'between:1,5',
            ],
        ];
    }

    public function status(): MeetingStatus
    {
        return MeetingStatus::from($this->validated('status'));
    }
}

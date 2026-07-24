<?php

namespace App\Http\Resources;

use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Meeting */
class AnswerFeedResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'meeting_id' => $this->id,
            'question' => $this->question,
            'answer' => $this->answer,
            'rating' => $this->rating,
            'asker' => $this->initiator->name,
        ];
    }
}

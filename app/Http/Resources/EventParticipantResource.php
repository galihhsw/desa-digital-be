<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventParticipantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event_id' => $this->event_id,
            'event' => new EventResource($this->event),
            'head_of_family_id' => $this->head_of_family_id,
            'head_of_family' => new HeadOfFamilyResource($this->headOfFamily),
            'quantity' => $this->quantity,
            'total_price' => (float)(string) $this->total_price,
            'payment_status' => $this->payment_status,
            'created_at' => $this->created_at,
            'snap_token' => $this->snap_token,
        ];
    }
}

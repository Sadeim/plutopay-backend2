<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'external_id' => $this->external_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,

            // Address
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country' => $this->country,

            // Computed
            'transactions_count' => $this->when(isset($this->transactions_count), $this->transactions_count),
            'total_spent' => $this->when(isset($this->total_spent), $this->total_spent),
            'total_spent_formatted' => $this->when(isset($this->total_spent), function () {
                return '$' . number_format(($this->total_spent ?? 0) / 100, 2);
            }),

            // Meta
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

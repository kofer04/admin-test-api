<?php

namespace App\DTO;

use App\Models\User;
use Illuminate\Http\Request;

class SettingParamsDTO
{
    public function __construct(
        public readonly string $key,
        public readonly mixed $value,
        public readonly ?int $userId = null,
    ) {}

    public static function fromRequest(Request $request, ?User $user = null): self
    {
        return new self(
            key: $request->input('key'),
            value: $request->input('value'),
            userId: $user?->id,
        );
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
            'user_id' => $this->userId,
        ];
    }
}


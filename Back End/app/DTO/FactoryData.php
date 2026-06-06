<?php

namespace App\DTO;

use App\Models\Factory;

class FactoryData
{
    public function __construct(
        public int $id,
        public string $factory_name,
        public string $location,
        public ?string $email,
        public ?string $website,
        public ?int $employees_count = null
    ) {}

    public static function fromModel(Factory $factory): self
    {
        return new self(
            $factory->id,
            $factory->factory_name,
            $factory->location,
            $factory->email,
            $factory->website,
            $factory->employees_count ?? $factory->employees()->count(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'factory_name' => $this->factory_name,
            'location' => $this->location,
            'email' => $this->email,
            'website' => $this->website,
            'employees_count' => $this->employees_count,
        ];
    }
}

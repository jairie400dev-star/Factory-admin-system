<?php

namespace App\DTO;

use App\Models\Employee;

class EmployeeData
{
    public function __construct(
        public int $id,
        public string $firstname,
        public string $lastname,
        public int $factory_id,
        public ?string $factory_name,
        public ?string $email,
        public ?string $phone
    ) {}

    public static function fromModel(Employee $employee): self
    {
        return new self(
            $employee->id,
            $employee->firstname,
            $employee->lastname,
            $employee->factory_id,
            $employee->factorys?->factory_name,
            $employee->email,
            $employee->phone,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'factory_id' => $this->factory_id,
            'factory_name' => $this->factory_name,
            'email' => $this->email,
            'phone' => $this->phone,
        ];
    }
}

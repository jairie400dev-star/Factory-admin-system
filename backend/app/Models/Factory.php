<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Employee;

class Factory extends Model
{
    use HasFactory;

    protected $table = 'factories';

    protected $fillable = [
        'factory_name',
        'location',
        'email',
        'website',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Factory;


class Employee extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeFactory> */
    use HasFactory;
protected $fillable = [
        'firstname',
        'lastname',
        'factory_id',
        'email',
        'phone',
    ];

    /**
     * An employee belongs to a factory.
     */
    public function factorys()
    {
        return $this->belongsTo(Factory::class, 'factory_id');
    }
}

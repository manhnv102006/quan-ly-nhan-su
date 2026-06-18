<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'departments';

    protected $fillable = [
        'manager_id',
        'department_code',
        'department_name',
        'description',
        'status',
    ];
}
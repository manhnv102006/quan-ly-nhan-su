<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;

    protected $table = 'departments';

    protected $fillable = [
        'manager_id',
        'department_code',
        'department_name',
        'description',
        'status',
    ];
    public function employees()
{
    return $this->hasMany(Employee::class, 'department_id');
}
}
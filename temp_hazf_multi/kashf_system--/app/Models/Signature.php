<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signature extends Model
{
    // هذا السطر هو الحل للمشكلة
    protected $fillable = ['title', 'name', 'is_active', 'responsibility_code'];
}

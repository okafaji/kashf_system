<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    // تحديد المفتاح الأساسي لأنه ليس id تلقائي بل employee_id
    protected $primaryKey = 'employee_id';
    public $incrementing = false; // لأن المفتاح ليس رقماً تلقائياً
    protected $keyType = 'string'; // لأن المفتاح نص

    // السماح بتعبئة هذه الحقول من الإكسل
    protected $fillable = [
        'employee_id',
        'name',
        'department',
        'job_title',
        'salary',
        'responsibility_no'
    ];

    // العلاقات
    public function payrolls()
    {
        return $this->hasMany(Payroll::class, 'employee_id', 'employee_id');
    }
}

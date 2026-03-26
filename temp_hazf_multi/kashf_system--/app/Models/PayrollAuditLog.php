<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollAuditLog extends Model
{
    protected $fillable = [
        'payroll_id',
        'kashf_no',
        'action',
        'description',
        'old_values',
        'new_values',
        'user_id',
        'user_name',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];
}

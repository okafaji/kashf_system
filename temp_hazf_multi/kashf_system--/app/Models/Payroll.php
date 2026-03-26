<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_READY_FOR_PRINT = 'ready_for_print';
    public const STATUS_PRINTED = 'printed';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'name', 'employee_id', 'department', 'admin_order_no', 'receipt_no', 'admin_order_date',
        'destination', 'city_id', 'governorate_id', 'job_title', 'mission_type_id',
        'start_date', 'end_date', 'days_count', 'daily_allowance',
        'accommodation_fee', 'transportation_fee', 'meals_count',
        'receipts_amount', 'is_half_allowance', 'total_amount',
        'kashf_no', 'order_year', 'group_no',
        'is_archived', 'status', 'notes', 'user_id', 'created_by_department_id'
    ];

    protected $casts = [
        'is_archived' => 'boolean',
    ];

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => 'مسودة',
            self::STATUS_READY_FOR_PRINT => 'جاهز للطباعة',
            self::STATUS_PRINTED => 'مطبوع',
            self::STATUS_ARCHIVED => 'مؤرشف',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    // العلاقات
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }

    public function missionType()
    {
        return $this->belongsTo(MissionType::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MissionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'responsibility_level',
        'daily_rate',
        'sort_order',
    ];

    protected $casts = [
        'daily_rate' => 'decimal:2',
    ];

    /**
     * Get the payrolls for this mission type.
     */
    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    /**
     * Get all unique mission type names
     */
    public static function getMissionTypeNames()
    {
        return self::query()
            ->selectRaw('name, MIN(sort_order) as sort_order')
            ->groupBy('name')
            ->orderBy('sort_order')
            ->pluck('name');
    }

    /**
     * Get all unique responsibility levels
     */
    public static function getResponsibilityLevels()
    {
        return self::query()
            ->selectRaw('responsibility_level, MIN(sort_order) as sort_order')
            ->groupBy('responsibility_level')
            ->orderBy('sort_order')
            ->pluck('responsibility_level');
    }

    /**
     * Get rate for specific mission type and responsibility level
     */
    public static function getRate($missionTypeName, $responsibilityLevel)
    {
        $record = self::where('name', $missionTypeName)
            ->where('responsibility_level', $responsibilityLevel)
            ->first();
        
        return $record ? $record->daily_rate : null;
    }
}

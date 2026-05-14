<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING_MANAGER = 'pending_manager';
    public const STATUS_MANAGER_APPROVED = 'manager_approved';
    public const STATUS_MANAGER_REJECTED = 'manager_rejected';
    public const STATUS_PENDING_HR = 'pending_hr';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'status',
        'manager_approved_at',
        'hr_finalized_at',
        'cancelled_by',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'manager_approved_at' => 'datetime',
            'hr_finalized_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approvals()
    {
        return $this->hasMany(LeaveApproval::class);
    }

    public static function businessDays(CarbonInterface $startDate, CarbonInterface $endDate): int
    {
        $days = 0;

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if (! $date->isWeekend()) {
                $days++;
            }
        }

        return $days;
    }
}

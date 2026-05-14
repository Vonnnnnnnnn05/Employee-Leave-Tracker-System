<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'default_days',
        'is_paid',
        'requires_balance',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_paid' => 'boolean',
            'requires_balance' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function balances()
    {
        return $this->hasMany(LeaveBalance::class);
    }
}

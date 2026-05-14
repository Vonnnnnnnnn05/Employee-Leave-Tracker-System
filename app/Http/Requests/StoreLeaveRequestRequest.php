<?php

namespace App\Http\Requests;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreLeaveRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'employee';
    }

    public function rules(): array
    {
        return [
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $startDate = $this->date('start_date');
                $endDate = $this->date('end_date');
                $totalDays = LeaveRequest::businessDays($startDate, $endDate);

                if ($totalDays < 1) {
                    $validator->errors()->add('start_date', 'The leave period must include at least one working day.');
                    return;
                }

                $overlap = LeaveRequest::query()
                    ->where('user_id', $this->user()->id)
                    ->whereNotIn('status', [
                        LeaveRequest::STATUS_MANAGER_REJECTED,
                        LeaveRequest::STATUS_REJECTED,
                        LeaveRequest::STATUS_CANCELLED,
                    ])
                    ->whereDate('start_date', '<=', $endDate)
                    ->whereDate('end_date', '>=', $startDate)
                    ->exists();

                if ($overlap) {
                    $validator->errors()->add('start_date', 'This leave request overlaps an existing active request.');
                    return;
                }

                $leaveType = LeaveType::find($this->integer('leave_type_id'));

                if ($leaveType?->requires_balance) {
                    $balance = LeaveBalance::query()
                        ->where('user_id', $this->user()->id)
                        ->where('leave_type_id', $leaveType->id)
                        ->first();

                    if (! $balance || $balance->remainingDays() < $totalDays) {
                        $validator->errors()->add('leave_type_id', 'Insufficient leave balance for the selected leave type.');
                    }
                }
            },
        ];
    }
}

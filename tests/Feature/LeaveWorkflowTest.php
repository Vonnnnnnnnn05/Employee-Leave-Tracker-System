<?php

namespace Tests\Feature;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_submit_valid_leave_request(): void
    {
        [$employee, , , $leaveType] = $this->makeWorkflowUsers();

        $this->actingAs($employee)
            ->post(route('employee.requests.store'), $this->requestPayload($leaveType))
            ->assertRedirect(route('employee.requests.index'));

        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'status' => LeaveRequest::STATUS_PENDING_MANAGER,
            'total_days' => 2,
        ]);

        $this->assertSame(2, $employee->leaveBalances()->first()->fresh()->pending_days);
    }

    public function test_employee_cannot_submit_overlapping_leave_request(): void
    {
        [$employee, , , $leaveType] = $this->makeWorkflowUsers();

        LeaveRequest::create([
            ...$this->requestPayload($leaveType),
            'user_id' => $employee->id,
            'total_days' => 2,
            'status' => LeaveRequest::STATUS_PENDING_MANAGER,
        ]);

        $this->actingAs($employee)
            ->from(route('employee.requests.create'))
            ->post(route('employee.requests.store'), $this->requestPayload($leaveType))
            ->assertRedirect(route('employee.requests.create'))
            ->assertSessionHasErrors('start_date');
    }

    public function test_employee_cannot_exceed_available_leave_balance(): void
    {
        [$employee, , , $leaveType] = $this->makeWorkflowUsers(1);

        $this->actingAs($employee)
            ->from(route('employee.requests.create'))
            ->post(route('employee.requests.store'), $this->requestPayload($leaveType))
            ->assertRedirect(route('employee.requests.create'))
            ->assertSessionHasErrors('leave_type_id');
    }

    public function test_manager_can_approve_only_team_requests(): void
    {
        [$employee, $manager, , $leaveType] = $this->makeWorkflowUsers();
        $otherManager = User::factory()->manager()->create();
        $leaveRequest = $this->createPendingRequest($employee, $leaveType);

        $this->actingAs($otherManager)
            ->patch(route('manager.requests.decide', $leaveRequest), ['decision' => 'approve'])
            ->assertForbidden();

        $this->actingAs($manager)
            ->patch(route('manager.requests.decide', $leaveRequest), ['decision' => 'approve'])
            ->assertRedirect();

        $this->assertSame(LeaveRequest::STATUS_PENDING_HR, $leaveRequest->fresh()->status);
        $this->assertDatabaseHas('leave_approvals', [
            'leave_request_id' => $leaveRequest->id,
            'approver_id' => $manager->id,
            'stage' => 'manager',
            'decision' => 'approved',
        ]);
    }

    public function test_hr_final_approval_deducts_balance(): void
    {
        [$employee, $manager, $admin, $leaveType] = $this->makeWorkflowUsers();
        $leaveRequest = $this->createPendingRequest($employee, $leaveType);

        $this->actingAs($manager)->patch(route('manager.requests.decide', $leaveRequest), ['decision' => 'approve']);
        $this->actingAs($admin)->patch(route('admin.requests.decide', $leaveRequest->fresh()), ['decision' => 'approve']);

        $balance = LeaveBalance::where('user_id', $employee->id)->where('leave_type_id', $leaveType->id)->first();

        $this->assertSame(LeaveRequest::STATUS_APPROVED, $leaveRequest->fresh()->status);
        $this->assertSame(0, $balance->fresh()->pending_days);
        $this->assertSame(2, $balance->fresh()->used_days);
    }

    public function test_rejected_leave_does_not_deduct_balance(): void
    {
        [$employee, $manager, $admin, $leaveType] = $this->makeWorkflowUsers();
        $leaveRequest = $this->createPendingRequest($employee, $leaveType);

        $this->actingAs($manager)->patch(route('manager.requests.decide', $leaveRequest), ['decision' => 'approve']);
        $this->actingAs($admin)->patch(route('admin.requests.decide', $leaveRequest->fresh()), ['decision' => 'reject']);

        $balance = LeaveBalance::where('user_id', $employee->id)->where('leave_type_id', $leaveType->id)->first();

        $this->assertSame(LeaveRequest::STATUS_REJECTED, $leaveRequest->fresh()->status);
        $this->assertSame(0, $balance->fresh()->pending_days);
        $this->assertSame(0, $balance->fresh()->used_days);
    }

    public function test_role_routes_are_protected(): void
    {
        [$employee, $manager, , ] = $this->makeWorkflowUsers();

        $this->actingAs($employee)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($manager)->get(route('employee.requests.index'))->assertForbidden();
    }

    private function makeWorkflowUsers(int $availableDays = 10): array
    {
        $manager = User::factory()->manager()->create();
        $admin = User::factory()->admin()->create();
        $employee = User::factory()->create(['manager_id' => $manager->id]);
        $leaveType = LeaveType::create([
            'name' => 'Vacation',
            'default_days' => $availableDays,
            'is_paid' => true,
            'requires_balance' => true,
            'is_active' => true,
        ]);

        LeaveBalance::create([
            'user_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'available_days' => $availableDays,
        ]);

        return [$employee, $manager, $admin, $leaveType];
    }

    private function createPendingRequest(User $employee, LeaveType $leaveType): LeaveRequest
    {
        $request = LeaveRequest::create([
            ...$this->requestPayload($leaveType),
            'user_id' => $employee->id,
            'total_days' => 2,
            'status' => LeaveRequest::STATUS_PENDING_MANAGER,
        ]);

        LeaveBalance::where('user_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->increment('pending_days', 2);

        return $request;
    }

    private function requestPayload(LeaveType $leaveType): array
    {
        return [
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->next('Monday')->format('Y-m-d'),
            'end_date' => now()->next('Tuesday')->format('Y-m-d'),
            'reason' => 'Family appointment',
        ];
    }
}

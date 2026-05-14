<?php

namespace Database\Seeders;

use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $vacation = LeaveType::firstOrCreate(['name' => 'Vacation'], ['default_days' => 15, 'is_paid' => true, 'requires_balance' => true, 'is_active' => true]);
        $sick = LeaveType::firstOrCreate(['name' => 'Sick Leave'], ['default_days' => 10, 'is_paid' => true, 'requires_balance' => true, 'is_active' => true]);
        $emergency = LeaveType::firstOrCreate(['name' => 'Emergency Leave'], ['default_days' => 5, 'is_paid' => true, 'requires_balance' => true, 'is_active' => true]);
        LeaveType::firstOrCreate(['name' => 'Unpaid Leave'], ['default_days' => 0, 'is_paid' => false, 'requires_balance' => false, 'is_active' => true]);

        $admin = User::factory()->admin()->create([
            'name' => 'HR Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'department' => 'Human Resources',
            'position' => 'HR Administrator',
        ]);

        $manager = User::factory()->manager()->create([
            'name' => 'Team Manager',
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
            'department' => 'Operations',
            'position' => 'Operations Manager',
        ]);

        $employee = User::factory()->create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => Hash::make('password'),
            'manager_id' => $manager->id,
            'department' => 'Operations',
            'position' => 'Operations Associate',
        ]);

        foreach ([$admin, $manager, $employee] as $user) {
            foreach ([$vacation, $sick, $emergency] as $type) {
                LeaveBalance::firstOrCreate(
                    ['user_id' => $user->id, 'leave_type_id' => $type->id],
                    ['available_days' => $type->default_days]
                );
            }
        }
    }
}

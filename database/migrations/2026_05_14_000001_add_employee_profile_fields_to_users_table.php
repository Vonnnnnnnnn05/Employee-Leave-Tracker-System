<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('employee')->after('password');
            $table->string('department')->nullable()->after('role');
            $table->foreignId('manager_id')->nullable()->after('department')->constrained('users')->nullOnDelete();
            $table->string('position')->nullable()->after('manager_id');
            $table->string('employment_status')->default('active')->after('position');
            $table->date('hire_date')->nullable()->after('employment_status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('manager_id');
            $table->dropColumn(['role', 'department', 'position', 'employment_status', 'hire_date']);
        });
    }
};

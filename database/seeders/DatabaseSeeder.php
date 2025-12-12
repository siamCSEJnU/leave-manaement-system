<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\LeaveRequest;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
   

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $admin = User::create([
            'employee_id'   => 'ADMIN001',
            'email'         => 'admin@company.com',
            'password'      => '123456',
            'first_name'    => 'Super',
            'last_name'     => 'Admin',
            'role'          => 'admin',
            'leave_balance' => 30,
            'is_active'     => true,
        ]);

        $manager = User::create([
            'employee_id'   => 'MGR001',
            'email'         => 'manager@company.com',
            'password'      => '123456',
            'first_name'    => 'Nahid',
            'last_name'     => 'Parvez',
            'role'          => 'manager',
            'leave_balance' => 25,
            'is_active'     => true,
        ]);

        $sifat = User::create([
            'employee_id'   => 'EMP001',
            'email'         => 'sifat@company.com',
            'password'      => '123456',
            'first_name'    => 'Sifat',
            'last_name'     => 'Ahmed',
            'role'          => 'employee',
            'manager_id'    => $manager->id,
            'leave_balance' => 20,
            'is_active'     => true,
        ]);

        $shakil = User::create([
            'employee_id'   => 'EMP002',
            'email'         => 'shakil@company.com',
            'password'      => '123456',
            'first_name'    => 'Shakil',
            'last_name'     => 'Ahmed',
            'role'          => 'employee',
            'manager_id'    => $manager->id,
            'leave_balance' => 18,
            'is_active'     => true,
        ]);

        // $sohel = User::create([
        //     'employee_id'   => 'EMP003',
        //     'email'         => 'sohel@company.com',
        //     'password'      => Hash::make('123456'),
        //     'first_name'    => 'Sohel',
        //     'last_name'     => 'Rana',
        //     'role'          => 'employee',
        //     'manager_id'    => $manager->id,
        //     'leave_balance' => 18,
        //     'is_active'     => true,
        // ]);

        LeaveRequest::create([
            'employee_id'   => $sifat->id,
            'leave_type'    => 'sick',
            'start_date'    => '2025-12-25',
            'end_date'      => '2025-12-27',
            'duration_days' => 3,
            'reason'        => 'Having fever and cold',
            'status'        => 'pending',
        ]);

        LeaveRequest::create([
            'employee_id'   => $shakil->id,
            'leave_type'    => 'vacation',
            'start_date'    => '2025-12-20',
            'end_date'      => '2025-12-24',
            'duration_days' => 5,
            'reason'        => 'Winter family vacation',
            'status'        => 'pending',
        ]);

        LeaveRequest::create([
            'employee_id'   => $sifat->id,
            'leave_type'    => 'personal',
            'start_date'    => '2025-11-10',
            'end_date'      => '2025-11-12',
            'duration_days' => 3,
            'reason'        => 'Personal matter',
            'status'        => 'approved',
            'reviewer_id'   => $manager->id,
            'reviewed_at'   => now(),
            'reviewer_notes'=> 'Approved',
        ]);
    }
}

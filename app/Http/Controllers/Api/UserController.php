<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query();
        if ($search = $request->query('search')) {
            $query->where('first_name', 'like', "%$search%")
                  ->orWhere('last_name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
        }
        return $query->paginate(20);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|unique:users',
            'email'       => 'required|email|unique:users',
            'password'    => 'required|min:6',
            'first_name'  => 'required',
            'last_name'   => 'required',
            'role'        => 'required|in:employee,manager,admin',
            'department'  => 'nullable|string',
            'manager_id'  => 'nullable|exists:users,id',
            'leave_balance' => 'required|integer|min:0',
        ]);

        $data['password'] = bcrypt($data['password']);

        $user = User::create($data);

        return response()->json([
            'message' => 'User created successfully',
            'user'    => $user
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return $user;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'first_name' => 'sometimes',
            'last_name' => 'sometimes',
            'role' => 'sometimes|in:employee,manager,admin',
            'department' => 'nullable',
            'manager_id' => 'nullable|exists:users,id',
            'leave_balance' => 'integer|min:0',
        ]);
        $user->update($data);
        return $user;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->update(['is_active' => false]); 
        return response()->json(['message' => 'User deactivated']);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {        
        $user = auth('api')->user();
        $query = LeaveRequest::query();

        if ($user->role === 'employee') {
            $query->where('employee_id', $user->id);
        } elseif ($user->role === 'manager') {
            $query->whereIn('employee_id', $user->teamMembers->pluck('id'));
        } 

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($start = $request->query('start_date')) {
            $query->where('start_date', '>=', $start);
        }

        return $query->paginate(20);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth('api')->user();

        $data = $request->validate([
            'leave_type' => 'required|in:sick,vacation,personal',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'required',
        ]);

        $start = Carbon::parse($data['start_date']);
        $end   = Carbon::parse($data['end_date']);
        $data['duration_days'] = $start->diffInDays($end) + 1;

        if ($data['duration_days'] > $user->leave_balance) {
            return response()->json(['error' => 'Insufficient leave balance'], 400);
        }

        $data['employee_id'] = $user->id;

        $leave = LeaveRequest::create($data);

        return response()->json($leave, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveRequest $leaf)  
    {
        $this->authorizeLeave($leaf);  
        return $leaf->load('comments');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveRequest $leaf)  
    {
        $this->authorizeLeave($leaf, 'update');  

        if ($leaf->status !== 'pending') {
            return response()->json(['error' => 'Cannot update non-pending request'], 400);
        }
        
        $data = $request->validate([
            'leave_type' => 'sometimes|in:sick,vacation,personal',
            'start_date' => 'sometimes|date|after_or_equal:today',
            'end_date'   => 'sometimes|date',
            'reason' => 'sometimes',
        ]);
        
        $startDate = $data['start_date'] ?? $leaf->start_date;
        $endDate   = $data['end_date']   ?? $leaf->end_date;

        if (Carbon::parse($endDate)->lt(Carbon::parse($startDate))) {
            return response()->json(['error' => 'End date cannot be before start date'], 422);
        }

        $newDuration = Carbon::parse($startDate)->diffInDays($endDate) + 1;

        if ($newDuration > $leaf->duration_days) {
            $extraDays = $newDuration - $leaf->duration_days;
            if ($extraDays > auth('api')->user()->leave_balance) {
                return response()->json(['error' => 'Not enough leave balance to extend leave'], 400);
            }
        }

        $data['duration_days'] = $newDuration;

        $leaf->update($data);

        return $leaf;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveRequest $leaf)  
    {
        $this->authorizeLeave($leaf, 'delete');  
        if ($leaf->status !== 'pending') {
            return response()->json(['error' => 'Cannot cancel non-pending request'], 400);
        }
        $leaf->update(['status' => 'cancelled']);
        return response()->json(['message' => 'Request cancelled']);
    }

    public function approve(Request $request, LeaveRequest $leaf)  
    {
        $user = auth('api')->user();

        $this->authorizeLeave($leaf, 'approve');  

        if ($leaf->status !== 'pending') {
            return response()->json(['error' => 'Cannot approve non-pending request'], 400);
        }

        $notes = $request->input('reviewer_notes');

        $employee = $leaf->employee;  
        if (!$employee) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        $employee->decrement('leave_balance', $leaf->duration_days);

        $leaf->update([
            'status'         => 'approved',
            'reviewer_id'    => $user->id,
            'reviewed_at'    => now(),
            'reviewer_notes' => $notes,
        ]);

        $leaf->refresh();

        return response()->json([
            'message' => 'Leave approved successfully',
            'leave'   => $leaf,
            'employee_remaining_balance' => $employee->refresh()->leave_balance
        ]);
    }

    public function reject(Request $request, LeaveRequest $leaf)  
    {
        $user = auth('api')->user();
        $this->authorizeLeave($leaf, 'reject');  
        if ($leaf->status !== 'pending') {
            return response()->json(['error' => 'Cannot reject non-pending request'], 400);
        }
        $data = $request->validate(['reviewer_notes' => 'required']);
        $leaf->update([
            'status' => 'rejected',
            'reviewer_id' => $user->id,
            'reviewed_at' => now(),
            'reviewer_notes' => $data['reviewer_notes'],
        ]);
        return $leaf;
    }

    protected function authorizeLeave(LeaveRequest $leaf, $action = 'view')  
    {
        $user = auth('api')->user();

        $employee = $leaf->employee;  

        if (!$employee) {
            abort(404, 'Employee not found');
        }

        $isEmployee = $leaf->employee_id === $user->id;  
        $isManager = $employee->manager_id === $user->id;
        $isAdmin = $user->role === 'admin';

        if (in_array($action, ['approve', 'reject']) && $leaf->employee_id === $user->id) {
            abort(403, 'You cannot approve/reject your own leave request');
        }

        if ($action === 'view' && ($isEmployee || $isManager || $isAdmin)) return;
        if ($action === 'update' && $isEmployee) return;
        if ($action === 'delete' && $isEmployee) return;        
        if (in_array($action, ['approve', 'reject']) && ($isManager || $isAdmin)) return;

        abort(403, 'Unauthorized');
    }
}
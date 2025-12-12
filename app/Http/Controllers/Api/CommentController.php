<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(LeaveRequest $leave)
    {
        $this->authorizeComment($leave);
        return $leave->comments()->orderBy('created_at')->get();
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, LeaveRequest $leave)
    {
        $this->authorizeComment($leave);
        $data = $request->validate(['comment_text' => 'required']);
        $comment = $leave->comments()->create([
            'user_id' => auth('api')->user()->id,
            'comment_text' => $data['comment_text'],
        ]);
        return response()->json($comment, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        if ($comment->user_id !== auth('api')->user()->id) {
            abort(403);
        }
       
        if ($comment->created_at->diffInHours(now()) > 1) {
            abort(400, 'Comment edit time expired');
        }
        $data = $request->validate(['comment_text' => 'required']);
        $comment->update($data);
        return $comment;
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        if ($comment->user_id !== auth('api')->user()->id) {
            abort(403);
        }
        $comment->delete();
        return response()->json(['message' => 'Comment deleted']);
    }

    protected function authorizeComment(LeaveRequest $leave)
    {
        $user = auth('api')->user();
        $isInvolved = $leave->employee_id === $user->id || $leave->employee->manager_id === $user->id || $user->role === 'admin';
        if (!$isInvolved) abort(403);
    }
}

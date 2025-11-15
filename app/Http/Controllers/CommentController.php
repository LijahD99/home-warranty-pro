<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    /**
     * Store a newly created comment.
     */
    public function store(Request $request, Ticket $ticket)
    {
        Gate::authorize('view', $ticket);

        $validated = $request->validate([
            'comment' => 'required|string|min:3',
            'is_internal' => 'nullable|boolean',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        // Only builders and admins can mark comments as internal
        // Silently force to false for homeowners instead of aborting
        $isInternal = false;
        if (isset($validated['is_internal']) && $validated['is_internal']) {
            if ($user->isBuilder() || $user->isAdmin()) {
                $isInternal = true;
            }
        }

        $comment = new Comment([
            'comment' => $validated['comment'],
            'is_internal' => $isInternal,
        ]);
        $comment->ticket_id = $ticket->id;
        $comment->user_id = $user->id;
        $comment->save();

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Comment added successfully!');
    }

    /**
     * Remove the specified comment.
     */
    public function destroy(Comment $comment)
    {
        /** @var \App\Models\User $user */
        $user = request()->user();

        // Only the comment author or admins can delete comments
        if ($comment->user_id !== $user->id && !$user->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $ticket_id = $comment->ticket_id;
        $comment->delete();

        return redirect()->route('tickets.show', $ticket_id)
            ->with('success', 'Comment deleted successfully!');
    }
}

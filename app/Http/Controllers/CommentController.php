<?php

namespace App\Http\Controllers;

use App\Http\Requests\Comment\CreateCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends Controller
{
    public function create(CreateCommentRequest $request, Task $task): CommentResource
    {
        $data = $request->validated();
        $user = $request->user();

        $comment = $task->comments()->create([
            'text' => $data['text'],
            'user_id' => $user->id,
            'task_id' => $task->id,
        ]);

        return new CommentResource($comment);
    }

    public function update(UpdateCommentRequest $request, Comment $comment): CommentResource
    {
        $data = $request->validated();
        $user = $request->user();

        if (!$comment->belongsToUser($user)) {
            abort(403);
        }

        $comment->update([
            'text' => $data['text'],
        ]);

        return new CommentResource($comment);
    }

    // Удаление комментария
    public function delete(Request $request, Comment $comment): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        if (!$comment->belongsToUser($user)) {
            abort(403);
        }

        $comment->delete();

        return response()->json(['message' => 'Комментарий удалён'], Response::HTTP_OK);
    }
}

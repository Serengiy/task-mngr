<?php

namespace App\Http\Controllers;

use App\Enums\StatusEnum;
use App\Http\Requests\Task\CreateTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Http\Resources\UserResource;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $user = $request->user();
        $tasksQuery = Task::query()
            ->with(Task::DEPENDENCIES)
          ->onlyAvailableForUser($user)
          ->filter($request->all());

        $tasks = $tasksQuery->paginate($request->get('per_page', 15));
        $statuses = StatusEnum::labels();
        return Inertia::render('tasks/Index', [
            'tasks' => [
                'data' => TaskResource::collection($tasks)->resolve(),
                'meta' => [
                    'current_page' => $tasks->currentPage(),
                    'last_page' => $tasks->lastPage(),
                    'per_page' => $tasks->perPage(),
                    'total' => $tasks->total(),
                ],
                'links' => [
                    'first' => $tasks->url(1),
                    'last' => $tasks->url($tasks->lastPage()),
                    'prev' => $tasks->previousPageUrl(),
                    'next' => $tasks->nextPageUrl(),
                ],
            ],
            'statuses' => $statuses,
        ]);
    }

    public function show(Request $request, Task $task): \Inertia\Response
    {
        if (!$task->isVisibleFor($request->user())) {
            abort(403);
        }

        $task->load(Task::DEPENDENCIES);
        $user = $request->user();

        return Inertia::render('tasks/Show', [
            'task' => TaskResource::make($task)->resolve(),
            'user' => UserResource::make($user)->resolve(),
        ]);
    }

    public function new(): \Inertia\Response
    {
        $statuses = StatusEnum::labels();
        return Inertia::render('tasks/Create', ['statuses' => $statuses]);
    }

    public function edit(Request $request, Task $task): \Inertia\Response
    {
        $user = $request->user();
        if ($user->id !== $task->creator_id) {
            abort(403);
        }
        $statuses = StatusEnum::labels();
        $users = User::all();
        return Inertia::render('tasks/Edit', [
            'statuses' => $statuses,
            'users' => UserResource::collection($users)->resolve(),
            'task' => TaskResource::make(
                $task->load(Task::DEPENDENCIES)
            )->resolve()
        ]);
    }

    public function create(CreateTaskRequest $request): \Inertia\Response
    {
        $data = $request->validated();
        $task = Task::query()->create([
            'name' => $data['name'],
            'status' => $data['status'],
            'description' => $data['description'],
            'due_date' => $data['due_date'],
            'responsible_id' => $data['responsible_id'],
            'creator_id' => $request->user()->id,
        ]);
        return Inertia::render('tasks/Show', [
            'task' => TaskResource::make($task->load(Task::DEPENDENCIES))->resolve()
        ]);
    }

    public function update(UpdateTaskRequest $request, Task $task): \Inertia\Response
    {
        $user = $request->user();
        if ($task->creator_id != $user->id) {
            abort(403);
        }
        $data = $request->validated();
        $task->update([
            'name' => $data['name'],
            'description' => $data['description'],
            'due_date' => $data['due_date'],
            'responsible_id' => $data['responsible_id'],
        ]);

        $task->participants()->sync($data['participants'] ?? []);

        return Inertia::render('tasks/Show', ['task' => TaskResource::make($task)->resolve()]);
    }

    public function delete(Request $request, Task $task): RedirectResponse
    {
        $task->delete();

        return redirect()
            ->route('home', $request->query())
            ->with('success', 'Задача удалена');
    }
}

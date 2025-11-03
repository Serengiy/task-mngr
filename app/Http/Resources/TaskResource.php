<?php

namespace App\Http\Resources;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Task
 */
class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status->label(),
            'creator_id' => $this->creator_id,
            'description' => $this->description,
            'created_at' => $this->created_at->format('Y-m-d'),
            'updated_at' => $this->updated_at->format('Y-m-d'),
            'due_date' => $this->due_date->format('Y-m-d'),
            'creator' => UserResource::make($this->creator)->resolve(),
            'responsible' => UserResource::make($this->responsible)->resolve(),
            'participants' => UserResource::collection($this->participants)->resolve(),
            'comments' => CommentResource::collection($this->comments)->resolve(),
        ];
    }
}

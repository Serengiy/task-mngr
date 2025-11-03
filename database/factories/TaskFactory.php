<?php

namespace Database\Factories;

use App\Enums\StatusEnum;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        $statuses = StatusEnum::cases();

        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement($statuses)->value,
            'creator_id' => User::factory(),
            'responsible_id' => User::factory(),
            'due_date' => $this->faker->dateTimeBetween('+1 week', '+2 months'),
        ];
    }

    public function withParticipants(int $count = 3): static
    {
        return $this->afterCreating(function (Task $task) use ($count) {
            $participants = User::factory($count)->create();
            $task->participants()->attach($participants);
        });
    }

    public function withComments(int $count = 5): static
    {
        return $this->afterCreating(function (Task $task) use ($count) {
            \App\Models\Comment::factory($count)->create([
                'task_id' => $task->id,
                'user_id' => $task->creator_id,
            ]);
        });
    }
}

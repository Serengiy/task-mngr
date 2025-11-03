<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetUpCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:set-up';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::query()->firstOrCreate([
            'email' => 'test@gmail.com',
        ], [
            'name' => fake()->name(),
            'password' => Hash::make('password'),
        ]);

        $otherUsers = User::factory(10)->create();

        Task::factory(5)
            ->withParticipants(2)
            ->withComments(3)
            ->create([
                'creator_id' => $user->id,
                'responsible_id' => fn() => $otherUsers->random()->id,
            ]);

        Task::factory(5)
            ->withParticipants(2)
            ->withComments(3)
            ->create([
                'creator_id' => fn() => $otherUsers->random()->id,
                'responsible_id' => $user->id,
            ]);

        Task::factory(5)
            ->withParticipants(3)
            ->withComments(3)
            ->create([
                'creator_id' => fn() => $otherUsers->random()->id,
                'responsible_id' => fn() => $otherUsers->random()->id,
            ])
            ->each(function (Task $task) use ($user) {
                $task->participants()->attach($user->id);
            });

        Task::factory(10)
            ->withParticipants(3)
            ->withComments(5)
            ->create([
                'creator_id' => fn() => $otherUsers->random()->id,
                'responsible_id' => fn() => $otherUsers->random()->id,
            ]);
    }
}

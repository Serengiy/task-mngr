<?php

namespace App\Models;

use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int|null $creator_id
 * @property int|null $responsible_id
 * @property StatusEnum $status
 * @property Carbon|null $due_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User|null $responsible
 * @property User $creator
 * @property Collection $participants
 * @property Collection|null $comments
 */
class Task extends Model
{
    use SoftDeletes, HasFactory;

    const DEPENDENCIES = ['creator', 'responsible', 'participants', 'comments.user'];
    protected $fillable = [
        'name',
        'description',
        'due_date',
        'status',
        'creator_id',
        'responsible_id',
    ];

    protected $casts = [
        'status' => StatusEnum::class,
        'due_date' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->withTimestamps();
    }

    public function isVisibleFor(User $user): bool
    {
        return $this->creator_id === $user->id
            || $this->responsible_id === $user->id
            || $this->participants->contains('id', $user->id);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        if ($name = $filters['name'] ?? null) {
            $query->where('name', 'like', "%{$name}%");
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', StatusEnum::from((int) $filters['status'])->value);
        }

        return $query;
    }

    public function scopeOnlyAvailableForUser(Builder $query, User $user): Builder
    {
        return $query->with(['creator', 'responsible', 'comments.user'])
        ->where(function ($query) use ($user) {
            $query->where('creator_id', $user->id)
                ->orWhere('responsible_id', $user->id)
                ->orWhereHas('participants', function ($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
        });
    }
}

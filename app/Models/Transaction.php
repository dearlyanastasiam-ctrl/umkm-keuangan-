<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = ['user_id', 'category_id', 'date', 'amount', 'description'];

    protected $casts = ['date' => 'date:Y-m-d', 'amount' => 'integer'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** Admin melihat semua data, pemilik hanya data miliknya. */
    public function scopeVisibleTo(Builder $q, User $user): Builder
    {
        return $user->isAdmin() ? $q : $q->where('user_id', $user->id);
    }
}

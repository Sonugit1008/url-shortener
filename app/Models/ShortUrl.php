<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShortUrl extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'original_url',
        'short_code',
        'clicks',
        'last_clicked_at',
    ];

    protected $casts = [
        'last_clicked_at' => 'datetime',
        'clicks' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getShortUrlAttribute(): string
    {
        return route('short-urls.redirect', $this->short_code);
    }
}

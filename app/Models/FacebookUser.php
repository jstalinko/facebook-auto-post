<?php

namespace App\Models;

use App\Models\FacebookPostLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacebookUser extends Model
{
    protected $fillable = [
        'user_id',
        'facebook_id',
        'name',
        'access_token',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(FacebookPage::class);
    }

    public function postLogs(): HasMany
    {
        return $this->hasMany(FacebookPostLog::class);
    }
}

<?php

namespace App\Models;

use App\Models\FacebookPostLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacebookPage extends Model
{
    protected $fillable = [
        'user_id',
        'facebook_user_id',
        'page_id',
        'page_name',
        'page_access_token',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function facebookUser(): BelongsTo
    {
        return $this->belongsTo(FacebookUser::class);
    }

    public function postLogs(): HasMany
    {
        return $this->hasMany(FacebookPostLog::class);
    }
}

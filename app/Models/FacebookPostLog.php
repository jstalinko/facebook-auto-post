<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacebookPostLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'facebook_user_id',
        'facebook_page_id',
        'job_id',
        'type',
        'message',
        'media_path',
        'scheduled_at',
        'status',
        'response',
        'error_message',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'response' => AsArrayObject::class,
    ];

    public function facebookPage()
    {
        return $this->belongsTo(FacebookPage::class);
    }

    public function facebookUser()
    {
        return $this->belongsTo(FacebookUser::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

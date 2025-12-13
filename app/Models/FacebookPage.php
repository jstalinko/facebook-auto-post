<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacebookPage extends Model
{
    protected $fillable = [
        'user_id',
        'page_id',
        'page_name',
        'page_access_token',
    ];
}

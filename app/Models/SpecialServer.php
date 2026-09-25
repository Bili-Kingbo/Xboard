<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialServer extends Model
{
    protected $table = 'v2_special_server';

    protected $guarded = ['id'];

    protected $casts = [
        'group_ids' => 'array',
        'client_routing_profile_ids' => 'array',
        'user_ids' => 'array',
        'tags' => 'array',
        'proxy_payload' => 'array',
        'show' => 'boolean',
        'sort' => 'integer',
    ];

    protected $appends = ['is_special'];

    public function getIsSpecialAttribute(): bool
    {
        return true;
    }
}

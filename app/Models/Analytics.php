<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Analytics extends Model
{
    protected $table = 'analytics';

    protected $fillable = [
        'application_id',
        'session',
        'method',
        'path',
        'query',
        'headers',
        'payload',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'query' => 'array',
        'headers' => 'array',
        'payload' => 'array',
    ];

    public const UPDATED_AT = null;
}

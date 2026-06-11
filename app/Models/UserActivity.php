<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_start',
        'session_end',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'session_start' => 'datetime',
        'session_end' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

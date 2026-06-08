<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SharedDashboard extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'user_id',
        'configuration',
        'expires_at',
    ];

    protected $casts = [
        'configuration' => 'array',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'jumlah_poin',
        'deskripsi',
    ];

    /**
     * Get the user that owns the point history.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

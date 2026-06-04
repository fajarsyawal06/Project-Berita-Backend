<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'event_type',
        'table_name',
        'description',
        'created_at',
    ];
}

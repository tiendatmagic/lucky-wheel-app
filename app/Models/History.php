<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $fillable = ['result', 'spun_at'];
    protected $casts = ['spun_at' => 'datetime'];
}

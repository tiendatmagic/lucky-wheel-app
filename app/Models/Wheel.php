<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wheel extends Model
{
    protected $keyType = 'string';
    protected $fillable = ['id', 'name', 'items'];
    protected $casts = ['items' => 'array'];
    public $incrementing = false;
}

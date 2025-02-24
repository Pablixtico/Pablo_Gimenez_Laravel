<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'request',
        'response',
        'status',
    ];

    protected $casts = [
        'request' => 'array',
        'response' => 'array',
    ];
}

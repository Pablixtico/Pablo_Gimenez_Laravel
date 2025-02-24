<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionWebhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'status',
        'webhook',
    ];

    protected $casts = [
        'webhook' => 'array',
    ];
}

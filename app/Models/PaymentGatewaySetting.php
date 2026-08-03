<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsEncryptedArrayObject;

class PaymentGatewaySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'gateway', 'is_enabled', 'config_json', 'sort_order',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'config_json' => AsEncryptedArrayObject::class,
    ];
}
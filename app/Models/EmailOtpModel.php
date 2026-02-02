<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailOtpModel extends Model
{
    protected $table = 'email_otps';

    protected $fillable = [
        'email',
        'otp',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime'
    ];
}

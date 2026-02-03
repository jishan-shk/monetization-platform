<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingModel extends Model
{
    protected $table = 'billings';

    protected $guarded = [];

    protected $casts = [
        'billing_month' => 'date:Y-m',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
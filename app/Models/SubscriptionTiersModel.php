<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionTiersModel extends Model
{
    protected $table = 'subscription_tiers';

    protected $fillable = [
        'name',
        'daily_limit',
        'extra_call_rate',
    ];
}

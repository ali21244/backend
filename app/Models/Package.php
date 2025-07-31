<?php

namespace App\Models;
use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Package extends Model
{
    use HasFactory;
      protected $fillable = [
        'user_id',
        'tracking_number',
        'description',
        'country',
        'origin_country',
        'destination_country',
        'weight',
        'status',
        'estimated_delivery',
        'shipping_method',
        'price',
        'insurance',
        'discount_applied',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

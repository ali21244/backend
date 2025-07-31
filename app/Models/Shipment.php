<?php

namespace App\Models;
use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shipment extends Model
{
    use HasFactory;
       protected $fillable = [
        'user_id',
        'tracking_number',
        'description',
        'country',
        'origin',
        'destination',
        'status',
        'weight',
        'price',
        'estimated_delivery',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

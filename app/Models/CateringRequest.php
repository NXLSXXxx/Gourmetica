<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CateringRequest extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'event_date',
        'event_type',
        'guests',
        'message',
        'status',
    ];
}

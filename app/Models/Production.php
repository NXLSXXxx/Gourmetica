<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAudit;

class Production extends Model
{
    use HasAudit;

    protected $fillable = [
        'user_id',
        'headquarter_id',
        'product_id',
        'quantity',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function headquarter()
    {
        return $this->belongsTo(Headquarter::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

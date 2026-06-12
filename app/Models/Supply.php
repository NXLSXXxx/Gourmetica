<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasAudit;

class Supply extends Model
{
    use HasAudit;

    protected $fillable = [
        'name',
        'unit',
    ];

    public function headquarters()
    {
        return $this->belongsToMany(Headquarter::class, 'headquarter_supply')
                    ->withPivot('stock')
                    ->withTimestamps();
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }
}

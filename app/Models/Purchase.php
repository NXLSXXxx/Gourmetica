<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'headquarter_id',
        'supplier_name',
        'supplier_ruc',
        'document_type',
        'series',
        'number',
        'total',
        'purchase_date',
    ];

    public function headquarter()
    {
        return $this->belongsTo(Headquarter::class);
    }
}

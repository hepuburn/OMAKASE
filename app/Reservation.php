<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $fillable = [
        'store_id',
        'date',
        'time',
    ];

    /**
     * 店舗とのリレーション
     *
     * @return belongsTo
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}

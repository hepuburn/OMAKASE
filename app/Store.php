<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Store extends Model
{
    protected $fillable = [
        'name',
        'store_no',
        'address'
    ];

    /**
     * 全ての予約日とのリレーション
     *
     * @return HasMany
     */
    public function reservations()
    {
        return $this->HasMany(Reservation::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWallet extends Model
{
    use HasFactory;

    protected $table = 'user_wallet';

    protected $fillable = ['user_id', 'default_currency'];

    public function rub()
    {
        return $this->hasOne(RubWallet::class, 'wallet_id', 'id');
    }

    public function euro()
    {
        return $this->hasOne(EuroWallet::class, 'wallet_id', 'id');
    }

    public function usd()
    {
        return $this->hasOne(UsdWallet::class, 'wallet_id', 'id');
    }
}

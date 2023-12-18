<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsdWallet extends Model
{
    use HasFactory;

    protected $table = 'usd_wallet';

    protected $fillable = ['wallet_id', 'sum'];

    public $timestamps = false;
}

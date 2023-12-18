<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $table = 'currency';

    protected $fillable = ['slug', 'to_rub'];

    public $timestamps = false;

    public static function getCourse(string $currency)
    {
        return self::select('to_rub')->where('slug', $currency)->first();
    }
}

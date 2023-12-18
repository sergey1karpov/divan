<?php

namespace App\Сurrencies\Contracts;

use App\Models\EuroWallet;
use App\Models\RubWallet;
use App\Models\UsdWallet;
use Illuminate\Http\Request;

interface WalletInterface
{
    public function getBalance(Request $request): RubWallet|EuroWallet|UsdWallet;

    public function creditToBalance(Request $request): void;

    public function writeOffBalance(Request $request): void;
}

<?php

namespace App\Сurrencies;

use App\Exceptions\NotEnoughMoneyException;
use App\Exceptions\NotFoundWalletException;
use App\Models\UsdWallet;
use App\Сurrencies\Contracts\TotalBalanceInterface;
use App\Сurrencies\Contracts\WalletInterface;
use Illuminate\Http\Request;

class Usd implements WalletInterface, TotalBalanceInterface
{
    /**
     * Return usd balance
     *
     * @param Request $request
     * @return UsdWallet
     * @throws NotFoundWalletException
     */
    public function getBalance(Request $request): UsdWallet
    {
        $wallet = UsdWallet::where('wallet_id', $request->wallet_id)->first();

        if(!$wallet) {
            throw new NotFoundWalletException("Wallet not found. Please, create the (usd) currency wallet");
        }

        return $wallet;
    }

    /**
     * Update usd balance
     *
     * @param Request $request
     * @return void
     * @throws NotFoundWalletException
     */
    public function creditToBalance(Request $request): void
    {
        $balance = $this->getBalance($request);

        UsdWallet::where('wallet_id', $request->wallet_id)->update(['sum' => $balance->sum + $request->sum]);
    }

    /**
     * Write off money
     *
     * @param Request $request
     * @return void
     * @throws NotEnoughMoneyException|NotFoundWalletException
     */
    public function writeOffBalance(Request $request): void
    {
        $balance = $this->getBalance($request);

        if($balance->sum < $request->sum) {
            throw new NotEnoughMoneyException('На вашем долларовом счете не достаточно денег');
        }

        UsdWallet::where('wallet_id', $request->wallet_id)->update(['sum' => $balance->sum - $request->sum]);
    }

    /**
     * Get total balance wallet in usd
     *
     * @param float $totalRubSum
     * @param float $usdCourse
     * @return float
     */
    public function getTotalBalanceInWallet(float $totalRubSum, float $usdCourse): float
    {
        return $totalRubSum / $usdCourse;
    }
}

<?php

namespace App\Сurrencies;

use App\Exceptions\NotEnoughMoneyException;
use App\Exceptions\NotFoundWalletException;
use App\Models\EuroWallet;
use App\Сurrencies\Contracts\TotalBalanceInterface;
use App\Сurrencies\Contracts\WalletInterface;
use Illuminate\Http\Request;

class Euro implements WalletInterface, TotalBalanceInterface
{
    /**
     * Return euro balance
     *
     * @param Request $request
     * @return EuroWallet
     * @throws NotFoundWalletException
     */
    public function getBalance(Request $request): EuroWallet
    {
        $wallet = EuroWallet::where('wallet_id', $request->wallet_id)->first();

        if(!$wallet) {
            throw new NotFoundWalletException("Wallet not found. Please, create the (euro) currency wallet");
        }

        return $wallet;
    }

    /**
     * Update euro balance
     *
     * @param Request $request
     * @return void
     * @throws NotFoundWalletException
     */
    public function creditToBalance(Request $request): void
    {
        $balance = $this->getBalance($request);

        EuroWallet::where('wallet_id', $request->wallet_id)->update(['sum' => $balance->sum + $request->sum]);
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
            throw new NotEnoughMoneyException('На вашем евро счете не достаточно денег');
        }

        EuroWallet::where('wallet_id', $request->wallet_id)->update(['sum' => $balance->sum - $request->sum]);
    }

    /**
     * Get total balance wallet in euro
     *
     * @param float $totalRubSum
     * @param float $euroCourse
     * @return float
     */
    public function getTotalBalanceInWallet(float $totalRubSum, float $euroCourse): float
    {
        return $totalRubSum / $euroCourse;
    }
}

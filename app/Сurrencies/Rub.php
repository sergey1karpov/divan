<?php

namespace App\Сurrencies;

use App\Exceptions\NotEnoughMoneyException;
use App\Exceptions\NotFoundWalletException;
use App\Models\RubWallet;
use App\Сurrencies\Contracts\WalletInterface;
use Illuminate\Http\Request;

class Rub implements WalletInterface
{
    /**
     * Return rub balance
     *
     * @param Request $request
     * @return RubWallet
     * @throws NotFoundWalletException
     */
    public function getBalance(Request $request): RubWallet
    {
        $wallet = RubWallet::where('wallet_id', $request->wallet_id)->first();

        if(!$wallet) {
            throw new NotFoundWalletException("Wallet not found. Please, create the (rub) currency wallet");
        }

        return $wallet;
    }

    /**
     * Update rub balance
     *
     * @param Request $request
     * @return void
     * @throws NotFoundWalletException
     */
    public function creditToBalance(Request $request): void
    {
        $balance = $this->getBalance($request);

        RubWallet::where('wallet_id', $request->wallet_id)->update(['sum' => $balance->sum + $request->sum]);
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
            throw new NotEnoughMoneyException('На вашем рублевом счете не достаточно денег');
        }

        RubWallet::where('wallet_id', $request->wallet_id)->update(['sum' => $balance->sum - $request->sum]);
    }
}

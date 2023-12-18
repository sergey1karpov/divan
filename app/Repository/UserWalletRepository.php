<?php

namespace App\Repository;

use App\Enums\Currency;
use App\Exceptions\NotFoundWalletException;
use App\Models\Currency as WalletCurrency;
use App\Exceptions\NotEnoughMoneyException;
use App\Models\EuroWallet;
use App\Models\RubWallet;
use App\Models\UsdWallet;
use App\Models\UserWallet;
use App\Сurrencies\Euro;
use App\Сurrencies\Rub;
use App\Сurrencies\Usd;
use Illuminate\Http\Request;

class UserWalletRepository
{
    public function __construct(
        private Rub $rub,
        private Euro $euro,
        private Usd $usd
    ) {}

    /**
     * Create new wallet
     *
     * @param Request $request
     * @return void
     */
    public function newWallet(Request $request): void
    {
        UserWallet::create([
            'user_id' => $request->user_id,
            'default_currency' => $request->default_currency
        ]);
    }

    /**
     * Create new currency wallet
     *
     * @param int $wallet_id
     * @param string $currency
     * @return void
     * @throws NotFoundWalletException
     */
    public function newCurrencyWallet(int $wallet_id, string $currency): void
    {
        $wallet = UserWallet::where('id', $wallet_id)->first();

        if(!$wallet) {
            throw new NotFoundWalletException('Wallet not found');
        }

        $target = 'App\Models\\' . ucfirst($currency) . 'Wallet';

        $target::updateOrCreate(
            ['wallet_id' => $wallet->id],
            ['wallet_id' => $wallet->id]
        );
    }

    /**
     * Update balance
     *
     * @param Request $request
     * @return void
     * @throws NotFoundWalletException
     */
    public function addMoney(Request $request): void
    {
        switch ($request->currency) {
            case Currency::EURO->value:
                $this->euro->creditToBalance($request);
                break;
            case Currency::USD->value:
                $this->usd->creditToBalance($request);
                break;
            case Currency::RUB->value:
                $this->rub->creditToBalance($request);
                break;
        }
    }

    /**
     * Write off from balance
     *
     * @param Request $request
     * @return void
     * @throws NotEnoughMoneyException|NotFoundWalletException
     */
    public function writeOffMoney(Request $request): void
    {
        switch ($request->currency) {
            case Currency::EURO->value:
                $this->euro->writeOffBalance($request);
                break;
            case Currency::USD->value:
                $this->usd->writeOffBalance($request);
                break;
            case Currency::RUB->value:
                $this->rub->writeOffBalance($request);
                break;
        }
    }

    /**
     * Get total balance
     *
     * @param Request $request
     * @return float|null
     * @throws NotFoundWalletException
     */
    public function getTotalBalance(Request $request)
    {
        $balance = UserWallet::where('user_id', $request->user_id)->first();

        if(!$balance) {
            throw new NotFoundWalletException("You doesn't have a wallet");
        }

        $euroCourse = WalletCurrency::getCourse(Currency::EURO->value);
        $usdCourse = WalletCurrency::getCourse(Currency::USD->value);

        $totalRubSum = $this->convertToRub($balance, $euroCourse->to_rub, $usdCourse->to_rub);

        if($request->currency) {
            return $this->returnTotalSum($request->currency, $totalRubSum, $usdCourse->to_rub, $euroCourse->to_rub);
        }

        return $this->returnTotalSum($balance->default_currency, $totalRubSum, $usdCourse->to_rub, $euroCourse->to_rub);
    }

    /**
     * Get total sum
     *
     * @param string $currency
     * @param float $totalRubSum
     * @param float $usdCourse
     * @param float $euroCourse
     * @return float|void
     */
    public function returnTotalSum(string $currency, float $totalRubSum, float $usdCourse, float $euroCourse)
    {
        switch ($currency) {
            case Currency::USD->value:
                return $this->usd->getTotalBalanceInWallet($totalRubSum, $usdCourse);
            case Currency::EURO->value:
                return $this->euro->getTotalBalanceInWallet($totalRubSum, $euroCourse);
            case Currency::RUB->value:
                return $totalRubSum;
        }
    }

    /**
     * Generate currency to rub
     *
     * @param UserWallet $userWallet
     * @param float $euroCourse
     * @param float $usdCourse
     * @return float
     */
    private function convertToRub(UserWallet $userWallet, float $euroCourse, float $usdCourse): float
    {
        $rub = $userWallet->rub->sum ?? 0;
        $usd = $userWallet->usd->sum ?? 0;
        $euro = $userWallet->euro->sum ?? 0;

        return ($usd * $usdCourse) + ($euro * $euroCourse) + $rub;
    }

    /**
     * Change currency
     *
     * @param Request $request
     * @return void
     */
    public function changeCurrency(Request $request): void
    {
        UserWallet::where('user_id', $request->user_id)->update([
            'default_currency' => $request->currency
        ]);
    }
}

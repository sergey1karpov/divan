<?php

namespace App\Repository;

use App\Enums\Currency as EnumCurrency;
use App\Exceptions\NotFoundWalletException;
use App\Models\Currency;
use App\Models\EuroWallet;
use App\Models\RubWallet;
use App\Models\UsdWallet;
use App\Models\UserWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BankRepository
{
    public function __construct(private UserWalletRepository $userWalletRepository) {}

    /**
     * Изменить валюту
     *
     * @param Request $request
     * @return void
     */
    public function changeCourse(Request $request): void
    {
        Currency::where('slug', $request->currency)->update([
            'to_rub' => $request->course
        ]);
    }

    /**
     * Convert old sum in new sum
     * Create new sum wallet, if need it
     * Transaction to sum and delete currency
     *
     * @param Request $request
     * @return void
     * @throws NotFoundWalletException
     */
    public function dropCurrency(Request $request): void
    {
        $euroCourse = Currency::getCourse(EnumCurrency::EURO->value);
        $usdCourse = Currency::getCourse(EnumCurrency::USD->value);

        $this->changeCurrency($request->new_currency);

        $wallets = $this->getWallets($request->drop_currency);

        foreach($wallets as $wallet) {
            $totalSumInRub = $this->totalSumInRub($request->drop_currency, $wallet, $euroCourse->to_rub, $usdCourse->to_rub);

            $convertSum = $this->convertSumInNewCurrency($request->new_currency, $totalSumInRub, $euroCourse->to_rub, $usdCourse->to_rub);

            $currentWallet = DB::table($request->new_currency . '_wallet')
                ->where('wallet_id', $wallet->wallet_id)
                ->exists();

            if (!$currentWallet) {
                $this->userWalletRepository->newCurrencyWallet($wallet->wallet_id, $request->new_currency);
            }

            $currentWallet = DB::table($request->new_currency . '_wallet')
                ->where('wallet_id', $wallet->wallet_id)
                ->first();

            DB::transaction(function () use ($request, $convertSum, $currentWallet) {
                DB::table($request->new_currency . '_wallet')
                    ->where('wallet_id', $currentWallet->wallet_id)
                    ->update(['sum' => $currentWallet->sum + $convertSum]);

                DB::table($request->drop_currency . '_wallet')
                    ->where('wallet_id', $currentWallet->wallet_id)
                    ->update(['sum' => 0]);

                DB::table('currency')->where('slug', $request->drop_currency)
                    ->delete();
            });
        }
    }

    /**
     * Установить новый курс для кошельков
     *
     * @param string $currency
     * @return void
     */
    public function changeCurrency(string $currency): void
    {
        $chunkSize = 200;

        UserWallet::query()->chunk($chunkSize, function ($models) use ($currency) {
            foreach ($models as $model) {
                $model->default_currency = $currency;
                $model->save();
            }
        });
    }

    /**
     * Получить все кошельки в валюте, которая будет отключена
     *
     * @param string $tablePrefix
     * @return Collection
     */
    public function getWallets(string $tablePrefix): Collection
    {
        return DB::table($tablePrefix . '_wallet')->get();
    }

    /**
     * Получить сумму в рублях с отключаемого кошелька
     *
     * @param string $currency
     * @param object $wallet
     * @param float $euroCourse
     * @param float $usdCourse
     * @return float
     */
    public function totalSumInRub(string $currency, object $wallet, float $euroCourse, float $usdCourse): float
    {
        $totalSumInRub = 0;

        if($currency == EnumCurrency::EURO->value) {
            $totalSumInRub = $wallet->sum * $euroCourse;
        } elseif ($currency == EnumCurrency::USD->value) {
            $totalSumInRub = $wallet->sum * $usdCourse;
        } elseif ($currency == EnumCurrency::RUB->value) {
            $totalSumInRub = $wallet->sum;
        }

        return $totalSumInRub;
    }

    /**
     * Конвертация рублей в новую валюту
     *
     * @param string $currency
     * @param float $totalSumInRub
     * @param float $euroCourse
     * @param float $usdCourse
     * @return float
     */
    public function convertSumInNewCurrency(string $currency, float $totalSumInRub, float $euroCourse, float $usdCourse): float
    {
        $convertSum = 0;

        if($currency == EnumCurrency::EURO->value) {
            $convertSum = $totalSumInRub / $euroCourse;
        } elseif($currency == EnumCurrency::USD->value) {
            $convertSum = $totalSumInRub / $usdCourse;
        } elseif($currency == EnumCurrency::RUB->value) {
            $convertSum = $totalSumInRub;
        }

        return $convertSum;
    }
}

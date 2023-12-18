<?php

namespace App\Сurrencies\Contracts;

interface TotalBalanceInterface
{
    public function getTotalBalanceInWallet(float $totalRubSum, float $euroCourse): float;
}

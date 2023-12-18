<?php

use App\Http\Controllers\BankController;
use App\Http\Controllers\UserWalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('/v1')->group(function () {
    Route::get('/get-balance', [UserWalletController::class, 'getBalance'])->name('get-balance');
    Route::post('/create-wallet', [UserWalletController::class, 'createNewUserWallet'])->name('create-wallet');
    Route::post('/create-currency-wallet', [UserWalletController::class, 'createCurrencyWallet'])->name('create-currency-wallet');
    Route::post('/add-money', [UserWalletController::class, 'addMoneyToUserWallet'])->name('add-money');
    Route::post('/write-off-money', [UserWalletController::class, 'writeOffMoneyFromUserWallet'])->name('write-off-money');
    Route::post('/change-currency', [UserWalletController::class, 'changeWalletCurrency'])->name('change-currency');
    Route::get('/get-currencies', [UserWalletController::class, 'getCurrencies'])->name('get-currencies');

    Route::prefix('/bank')->group(function () {
        Route::post('/change-currency-course', [BankController::class, 'changeCurrencyCourse'])->name('change-currency-course');
        Route::post('/drop-currency', [BankController::class, 'dropCurrencyAndWallets'])->name('drop-currency');
    });
});

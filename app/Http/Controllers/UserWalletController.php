<?php

namespace App\Http\Controllers;

use App\Exceptions\NotEnoughMoneyException;
use App\Exceptions\NotFoundWalletException;
use App\Models\Currency;
use App\Repository\UserWalletRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * @OA\Info(title="User wallet API", version="0.1")
 */
class UserWalletController extends Controller
{
    public function __construct(private UserWalletRepository $userWalletRepository) {}

    /**
     * Получение суммы всех кошельков
     * @OA\Get(
     *       path="/api/v1/get-balance",
     *       operationId="UserWallet",
     *       tags={"UserWallet"},
     *       summary="Получение суммы всех кошельков",
     *       @OA\Parameter(
     *             name="user_id",
     *             description="id юзера",
     *             required=true,
     *             in="query",
     *             @OA\Schema(
     *                 type="integer"
     *             )
     *       ),
     *       @OA\Parameter(
     *              name="currency",
     *              description="В какой валюте (usd, euro, rub или валюта по умолчанию)",
     *              required=false,
     *              in="query",
     *              @OA\Schema(
     *                  type="string"
     *              )
     *       ),
     *       @OA\Response(
     *           response="200",
     *           description="OK",
     *           @OA\JsonContent(
     *                @OA\Property(
     *                    property="message",
     *                    type="boolean",
     *                ),
     *           ),
     *       )
     *   )
     * @param Request $request
     * @return JsonResponse
     * @throws NotFoundWalletException
     */
    public function getBalance(Request $request): JsonResponse
    {
        $balance =  $this->userWalletRepository->getTotalBalance($request);

        return response()->json($balance);
    }

    /**
     * Создание нового кошелька
     * @OA\Post(
     *       path="/api/v1/create-wallet",
     *       operationId="UserWalletCreate",
     *       tags={"UserWallet"},
     *       summary="Создание нового счета",
     *       @OA\Parameter(
     *             name="user_id",
     *             description="id юзера",
     *             required=true,
     *             in="query",
     *             @OA\Schema(
     *                 type="integer"
     *             )
     *       ),
     *       @OA\Parameter(
     *              name="default_currency",
     *              description="Валюта кошелька(usd, euro, rub)",
     *              required=true,
     *              in="query",
     *              @OA\Schema(
     *                  type="string"
     *              )
     *       ),
     *       @OA\Response(
     *           response="200",
     *           description="OK",
     *           @OA\JsonContent(
     *                @OA\Property(
     *                    property="message",
     *                    type="string",
     *                ),
     *           ),
     *       )
     *   )
     * @param Request $request
     * @return JsonResponse
     */
    public function createNewUserWallet(Request $request): JsonResponse
    {
        $this->userWalletRepository->newWallet($request);

        return response()->json('Wallet created');
    }

    /**
     * Создание кошелька под валюту
     * @OA\Post(
     *       path="/api/v1/create-currency-wallet",
     *       operationId="UserWalletCurrencyCreate",
     *       tags={"UserWallet"},
     *       summary="Создание кошелька под валюту",
     *       @OA\Parameter(
     *             name="wallet_id",
     *             description="id счета",
     *             required=true,
     *             in="query",
     *             @OA\Schema(
     *                 type="integer"
     *             )
     *       ),
     *       @OA\Parameter(
     *              name="currency",
     *              description="Валюта кошелька(usd, euro, rub)",
     *              required=true,
     *              in="query",
     *              @OA\Schema(
     *                  type="string"
     *              )
     *       ),
     *       @OA\Response(
     *           response="200",
     *           description="OK",
     *           @OA\JsonContent(
     *                @OA\Property(
     *                    property="message",
     *                    type="string",
     *                ),
     *           ),
     *       )
     *   )
     * @param Request $request
     * @return JsonResponse
     * @throws NotFoundWalletException
     */
    public function createCurrencyWallet(Request $request): JsonResponse
    {
        $this->userWalletRepository->newCurrencyWallet($request->wallet_id, $request->currency);

        return response()->json('Currency wallet created');
    }

    /**
     * Пополнение кошелька
     * @OA\Post(
     *       path="/api/v1/add-money",
     *       operationId="AddMoney",
     *       tags={"UserWallet"},
     *       summary="Пополнение кошелька",
     *       @OA\Parameter(
     *             name="wallet_id",
     *             description="id счета",
     *             required=true,
     *             in="query",
     *             @OA\Schema(
     *                 type="integer"
     *             )
     *       ),
     *       @OA\Parameter(
     *              name="currency",
     *              description="Валюта кошелька(usd, euro, rub)",
     *              required=true,
     *              in="query",
     *              @OA\Schema(
     *                  type="string"
     *              )
     *       ),
     *       @OA\Parameter(
     *               name="sum",
     *               description="Сумма пополнения",
     *               required=true,
     *               in="query",
     *               @OA\Schema(
     *                   type="integer"
     *               )
     *       ),
     *       @OA\Response(
     *           response="200",
     *           description="OK",
     *           @OA\JsonContent(
     *                @OA\Property(
     *                    property="message",
     *                    type="string",
     *                ),
     *           ),
     *       )
     *   )
     * @param Request $request
     * @return JsonResponse
     * @throws NotFoundWalletException
     */
    public function addMoneyToUserWallet(Request $request): JsonResponse
    {
        $this->userWalletRepository->addMoney($request);

        return response()->json('Wallet updated');
    }

    /**
     * Списать с кошелька
     * @OA\Post(
     *       path="/api/v1/write-off-money",
     *       operationId="WriteOffMoney",
     *       tags={"UserWallet"},
     *       summary="Списать с кошелька",
     *       @OA\Parameter(
     *             name="wallet_id",
     *             description="id счета",
     *             required=true,
     *             in="query",
     *             @OA\Schema(
     *                 type="integer"
     *             )
     *       ),
     *       @OA\Parameter(
     *              name="currency",
     *              description="Валюта кошелька(usd, euro, rub)",
     *              required=true,
     *              in="query",
     *              @OA\Schema(
     *                  type="string"
     *              )
     *       ),
     *       @OA\Parameter(
     *               name="sum",
     *               description="Сумма списания",
     *               required=true,
     *               in="query",
     *               @OA\Schema(
     *                   type="integer"
     *               )
     *       ),
     *       @OA\Response(
     *           response="200",
     *           description="OK",
     *           @OA\JsonContent(
     *                @OA\Property(
     *                    property="message",
     *                    type="string",
     *                ),
     *           ),
     *       )
     *   )
     * @param Request $request
     * @return JsonResponse
     * @throws NotFoundWalletException
     * @throws NotEnoughMoneyException
     */
    public function writeOffMoneyFromUserWallet(Request $request): JsonResponse
    {
        $this->userWalletRepository->writeOffMoney($request);

        return response()->json('Wallet minuses');
    }

    /**
     * Изменить валюту кошелька
     * @OA\Post(
     *       path="/api/v1/change-currency",
     *       operationId="ChangeCcurrency",
     *       tags={"UserWallet"},
     *       summary="Изменить валюту кошелька",
     *       @OA\Parameter(
     *             name="user_id",
     *             description="id юзера",
     *             required=true,
     *             in="query",
     *             @OA\Schema(
     *                 type="integer"
     *             )
     *       ),
     *       @OA\Parameter(
     *              name="currency",
     *              description="Валюта кошелька(usd, euro, rub)",
     *              required=true,
     *              in="query",
     *              @OA\Schema(
     *                  type="string"
     *              )
     *       ),
     *       @OA\Response(
     *           response="200",
     *           description="OK",
     *           @OA\JsonContent(
     *                @OA\Property(
     *                    property="message",
     *                    type="string",
     *                ),
     *           ),
     *       )
     *   )
     * @param Request $request
     * @return JsonResponse
     */
    public function changeWalletCurrency(Request $request): JsonResponse
    {
        $this->userWalletRepository->changeCurrency($request);

        return response()->json('Wallet currency updated');
    }

    /**
     * Получить все поддерживаемые валюты
     * @OA\Get(
     *       path="/api/v1/get-currencies",
     *       operationId="GetCurrencies",
     *       tags={"UserWallet"},
     *       summary="Получить все поддерживаемые валюты",
     *       @OA\Response(
     *           response="200",
     *           description="OK",
     *           @OA\JsonContent(
     *                @OA\Property(
     *                    property="message",
     *                    type="string",
     *                ),
     *           ),
     *       )
     *   )
     * @return Collection
     */
    public function getCurrencies(): Collection
    {
        $currency = Currency::select('slug')->get();

        return collect(json_decode($currency, true))->pluck('slug');
    }
}

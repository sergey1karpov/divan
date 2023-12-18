<?php

namespace App\Http\Controllers;

use App\Exceptions\NotFoundWalletException;
use App\Models\Currency;
use App\Repository\BankRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BankController extends Controller
{
    public function __construct(private BankRepository $bankRepository) {}

    /**
     * Изменить курс
     * @OA\Post(
     *       path="/api/v1/bank/change-currency-course",
     *       operationId="ChangeCourse",
     *       tags={"Bank"},
     *       summary="Изменить курс",
     *       @OA\Parameter(
     *             name="currency",
     *             description="Валюта (euro, usd)",
     *             required=true,
     *             in="query",
     *             @OA\Schema(
     *                 type="string"
     *             )
     *       ),
     *       @OA\Parameter(
     *              name="course",
     *              description="Курс к рублю",
     *              required=true,
     *              in="query",
     *              @OA\Schema(
     *                  type="integer"
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
     */
    public function changeCurrencyCourse(Request $request): JsonResponse
    {
        $this->bankRepository->changeCourse($request);

        return response()->json('Course updated');
    }

    /**
     * Отключить валюту, конвертировать в другую
     * @OA\Post(
     *       path="/api/v1/bank/drop-currency",
     *       operationId="ChangeCourseAndDrop",
     *       tags={"Bank"},
     *       summary="Отключить валюту, конвертировать в другую",
     *       @OA\Parameter(
     *             name="drop_currency",
     *             description="Валюта (rub, euro, usd) которую отключаем",
     *             required=true,
     *             in="query",
     *             @OA\Schema(
     *                 type="string"
     *             )
     *       ),
     *       @OA\Parameter(
     *              name="new_currency",
     *              description="Валюта (rub, euro, usd) в которую конвертируем",
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
     *                    type="boolean",
     *                ),
     *           ),
     *       )
     *   )
     * @param Request $request
     * @return JsonResponse
     * @throws NotFoundWalletException
     */
    public function dropCurrencyAndWallets(Request $request): JsonResponse
    {
        $this->bankRepository->dropCurrency($request);

        return response()->json('deleted');
    }
}

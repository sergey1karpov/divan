<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class NotEnoughMoneyException extends Exception
{
    /**
     * @return JsonResponse
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'error' => $this->getMessage()
        ]);
    }
}

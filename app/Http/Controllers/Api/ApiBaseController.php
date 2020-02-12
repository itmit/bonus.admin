<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse as JsonResponseAlias;

/**
 * Представляет базовый класс для api контроллеров.
 *
 * @package App\Http\Controllers\Api
 */
abstract class ApiBaseController extends Controller
{
    /**
     * Отправляет ответ на успешный запрос к api.
     *
     * @param array $result
     * @param string $message
     * @return JsonResponseAlias
     */
    public function sendResponse(array $result, string $message, $success = true): JsonResponseAlias
    {
        return response()->json([
            'success' => $success,
            'data' => $result,
            'message' => $message,
        ], 200);
    }

    /**
     * Отправляет ответ на запрос к api в случае ошибки.
     *
     * @param string $error
     * @param array $errorMessages
     * @param int $code
     * @return JsonResponseAlias
     */
    public function sendError(string $error, $errorMessages = [], int $code = 404): JsonResponseAlias
    {
        $response = [
            'success' => false,
            'data' => $errorMessages,
            'message' => $error,
        ];

        return response()->json($response, $code);
    }
}

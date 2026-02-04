<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

trait HasResponse
{
    public function successResponse(string $message = '', array $data = []): JsonResponse
    {
        return response()->json(
            [
                'status' => 'success',
                'message' => $message,
                'data' => $data,
            ],
            Response::HTTP_OK
        );
    }

    public function serverErrorResponse(?string $message = "An unexpected error occurred. Please try again later."): JsonResponse
    {
        return response()->json(
            [
                'status' => 'error',
                'message' => $message,
            ],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    public function badRequestResponse(string $message): JsonResponse
    {
        return response()->json(
            [
                'status' => 'error',
                'message' => $message,
            ],
            Response::HTTP_BAD_REQUEST
        );
    }

    public function validationErrorResponse(string $attribute, string $message): JsonResponse
    {
        return response()->json(
            [
                'message' => $message,
                'errors' => [
                    $attribute => [$message],
                ],
            ],
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }
}

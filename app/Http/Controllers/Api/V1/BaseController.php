<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

abstract class BaseController extends Controller
{
    /**
     * Return a successful JSON response
     */
    protected function successResponse($data, ?string $message = null, ?array $meta = null): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $data,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        if ($meta) {
            $response['meta'] = $meta;
        }

        return response()->json($response);
    }

    /**
     * Return an error JSON response
     */
    protected function errorResponse(?string $message = null, int $statusCode = 422, ?array $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message ?? 'An error occurred',
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }
}


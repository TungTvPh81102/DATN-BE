<?php

namespace App\Traits;

use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use JsonSerializable;

trait ApiResponseTrait
{
    private ?array $defaultSuccessData = [
        'success' => true,
    ];

    private function apiResponse(array $data, int $code = 200): JsonResponse
    {
        return response()->json(data: $data, status: $code);
    }

    private function morphToArray(array|Arrayable|JsonSerializable|null $data): ?array
    {
        if ($data instanceof Arrayable) {
            return $data->toArray();
        }

        if ($data instanceof JsonSerializable) {
            return $data->jsonSerialize();
        }

        return $data;
    }

    private function morphMessage(string|Exception $message): string
    {
        return $message instanceof Exception
            ? $message->getMessage()
            : $message;
    }

    public function respondSuccess(?string $message = null, mixed $contents = null): JsonResponse
    {
        $contents = $this->morphToArray($contents) ?? [];

        $data = empty($contents) ? $this->defaultSuccessData : $contents;

        return $this->apiResponse([
            'message' => $message,
            'data' => $data,
        ]);
    }

    public function respondOk(string $message, mixed $data = null): JsonResponse
    {
        $response = ['message' => $message];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        return $this->apiResponse($response);
    }

    public function respondCreated(?string $message = null, mixed $data = null): JsonResponse
    {
        return $this->apiResponse([
            'message' => $message,
            'data' => $this->morphToArray($data) ?? [],
        ], Response::HTTP_CREATED);
    }

    public function respondNotFound(string|Exception $message, ?string $key = 'message'): JsonResponse
    {
        return $this->apiResponse([
            $key => $this->morphMessage($message),
        ], Response::HTTP_NOT_FOUND);
    }

    public function respondUnauthorized(?string $message = null): JsonResponse
    {
        return $this->apiResponse([
            'message' => $message ?? 'Unauthenticated',
        ], Response::HTTP_UNAUTHORIZED);
    }

    public function respondForbidden(?string $message = null): JsonResponse
    {
        return $this->apiResponse([
            'message' => $message ?? 'Forbidden',
        ], Response::HTTP_FORBIDDEN);
    }

    public function respondError(?string $message = null, mixed $data = null): JsonResponse
    {
        $response = ['message' => $message];

        if (!is_null($data)) {
            $response['data'] = $data;
        }

        return $this->apiResponse($response, Response::HTTP_BAD_REQUEST);
    }

    public function respondServerError(?string $message = null): JsonResponse
    {
        return $this->apiResponse([
            'message' => $message ?? 'Internal Server Error',
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function respondValidationFailed(string|Exception $message, mixed $errors = null, ?string $key = 'message'): JsonResponse
    {
        $data = [
            $key => $this->morphMessage($message),
        ];

        if ($errors) {
            $data['errors'] = $errors;
        }

        return $this->apiResponse($data, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function respondNoContent(mixed $data = null): JsonResponse
    {
        return $this->apiResponse(
            $this->morphToArray($data) ?? [],
            Response::HTTP_NO_CONTENT
        );
    }

    public function respondTeapot(): JsonResponse
    {
        return $this->apiResponse([
            'message' => "I'm a teapot",
        ], Response::HTTP_I_AM_A_TEAPOT);
    }

    public function setDefaultSuccessResponse(?array $content = null): self
    {
        $this->defaultSuccessData = $content ?? [];
        return $this;
    }
}

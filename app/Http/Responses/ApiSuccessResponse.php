<?php

namespace App\Http\Responses;

use App\Utils\Logger;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiSuccessResponse implements Responsable
{
    public function __construct(
        protected mixed $data,
        protected mixed $metadata = 'success',
        protected mixed $logMessage = null,
        protected int $code = Response::HTTP_OK,
        protected array $headers = [],
    ) {
    }

    /**
     * Creates HTTP response that represents the object
     *
     * @param  Request  $request
     */
    public function toResponse($request): JsonResponse
    {
        if (is_array($this->logMessage)) {
            $this->logMessage = json_encode($this->logMessage);
        }
        Logger::info($this->logMessage);

        return response()->json([
            'data' => $this->data,
            'metadata' => $this->metadata,
        ], $this->code, $this->headers);
    }
}

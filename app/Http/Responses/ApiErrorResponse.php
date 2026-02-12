<?php

namespace App\Http\Responses;

use Throwable;
use App\Utils\Logger;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Exceptions\ExpectedException;
use Illuminate\Contracts\Support\Responsable;

class ApiErrorResponse implements Responsable
{
    public function __construct(
        protected string $message,
        protected ?Throwable $throwable = null,
        protected ?ExpectedException $exception = null,
        protected int $code = 500,
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
        $response = [
            'meta' => [
                'message' => $this->message,
            ],
            'data' => null,
        ];

        $this->throwable = $this->exception ?: $this->throwable;

        if ($this->throwable && config('app.debug') && config('app.env') === "local") {
            $response['debug'] = [
                'exception_message' => $this->throwable->getMessage(),
                'file' => $this->throwable->getFile(),
                'line' => $this->throwable->getLine(),
            ];
        }

        if ($this->throwable) {
            Logger::error($this->throwable);
        }

        if ($this->throwable && $this->throwable instanceof ExpectedException) {
            $this->code = 400;
        }

        return response()->json($response, $this->code, $this->headers);
    }
}

<?php

namespace App\Utils;

use Exception;
use Illuminate\Support\Facades\Log;
use Throwable;

class Logger
{
    /**
     * Log any information needed to be logger for example,
     * Request Payload, Http Response, Debugging messages etc
     */
    public static function info($message, $context = null)
    {
        // Handle primary message
        if (is_array($message) || is_object($message)) {
            $message = json_encode($message, JSON_PRETTY_PRINT);
        }

        // Handle context
        $logMessage = $message;
        if ($context !== null) {
            $processedContext = is_scalar($context)
                ? $context
                : json_encode($context, JSON_PRETTY_PRINT);
            $logMessage .= " | Context: " . $processedContext;
        }

        Log::info($logMessage);
    }

    /**
     * Logs custom exceptions passed to it or throwable
     */
    public static function error(?Throwable $throwable = null, ?Exception $exception = null)
    {
        $exceptionThrowable = $throwable ?: $exception;
        
        if ($exceptionThrowable === null) {
            Log::error("Logger::error() called without exception or throwable");
            return;
        }
        
        Log::error("{$exceptionThrowable->getMessage()} @ {$exceptionThrowable->getFile()}: {$exceptionThrowable->getLine()}");
    }
}

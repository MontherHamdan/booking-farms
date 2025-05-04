<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait ExceptionLoggerTrait
{
    /**
     * Log an exception with additional context.
     *
     * @param \Exception $e
     * @param array $context
     * @return void
     */
    public function logException(\Exception $e, array $context = [])
    {
        Log::error($e->getMessage(), $context);
    }
}

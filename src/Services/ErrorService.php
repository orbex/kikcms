<?php
declare(strict_types=1);

namespace KikCMS\Services;


use KikCMS\Config\KikCMSConfig;
use Phalcon\Config;
use Phalcon\Di\Injectable;
use stdClass;

/**
 * @property Config config
 */
class ErrorService extends Injectable
{
    /**
     * @param $error
     * @param bool $isProduction
     * @return string|null
     */
    public function getErrorView($error, bool $isProduction): ?string
    {
        if ( ! $error) {
            return null;
        }

        $isRecoverableError = $this->isRecoverableError($error);

        // don't show recoverable errors in production
        if ($isProduction && $isRecoverableError) {
            return null;
        }

        http_response_code(500);

        if ($this->isAjaxRequest() && ! $isProduction) {
            return 'error500content';
        }

        return 'show500';
    }

    /**
     * @param mixed $error
     */
    public function handleError($error)
    {
        $isProduction = $this->config->application->env === KikCMSConfig::ENV_PROD;

        if( ! $errorView = $this->getErrorView($error, $isProduction)){
            return;
        }

        echo $this->view->getRender('errors', $errorView, ['error' => $isProduction ? null : $error]);
    }

    /**
     * @return bool
     */
    private function isAjaxRequest(): bool
    {
        $ajaxHeader = 'HTTP_X_REQUESTED_WITH';

        return ! empty($_SERVER[$ajaxHeader]) && strtolower($_SERVER[$ajaxHeader]) == 'xmlhttprequest';
    }

    /**
     * @param stdClass|array $error
     * @return null|int
     */
    private function getErrorType($error): ?int
    {
        if (is_object($error)) {
            return $error->type ?? null;
        }

        return $error['type'] ?? null;
    }

    /**
     * @param stdClass|array $error
     * @return bool
     */
    private function isRecoverableError($error): bool
    {
        if ( ! $errorType = $this->getErrorType($error)) {
            return false;
        }

        return ! in_array($errorType, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR]);
    }
}
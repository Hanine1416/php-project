<?php

namespace MBComponents\MiddleWare;

use MBComponents\Slim;
use Slim\Middleware;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\ExceptionHandler;
use Throwable;

/**
 * This class handle app crash and send an email to the dev team
 * Class MiddlewareError
 * @package middlewares
 */
class MiddlewareError extends Middleware
{
    /**
     * @var Slim $app
     */
    protected $app;

    /**
     * Handle not found page or any other exception
     *
     * Perform actions specific to this middleware and optionally
     * Call the next downstream middleware.
     */
    public function call()
    {
        try {
            $this->next->call();
        } catch (\Exception|Throwable $e) {
            $logger = $this->app->getMonoLog();
            $debug = $this->app->settings['debug'];
            /**
             * Check environment to show error page for dev or prod
             * @var string $environment
             */
            $environment = $debug ? 'dev' : 'prod';
            /** Check if the error is throwable or simple exception */
            if ($e instanceof Throwable) {
                $e = FlattenException::create(new FatalThrowableError($e));
                $e->setCode(500);
            } else {
                $e = FlattenException::create($e);
            }
            /** Create log for this error if debug mode on otherwise show error page */
            if ($debug) {
                $logger->log('crit', $environment, $e->getCode(), ['message' => $e->getMessage()]);
                $exceptionHandler = new ExceptionHandler();
                $exceptionHandler->sendPhpResponse($e);
            } else {
                /** Render error page with exception info */
                $this->app->render('exceptions/error.html.twig', [
                    'exception' => $e
                ]);
            }
        }
    }
}

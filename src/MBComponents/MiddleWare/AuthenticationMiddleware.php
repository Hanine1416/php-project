<?php
/**
 * Created by PhpStorm.
 * User: mobelite
 * Date: 20/06/2018
 * Time: 17:22
 */

namespace MBComponents\MiddleWare;

use MBComponents\Slim;
use Slim\Middleware;

/**
 * Protect route with Authentication Guard
 *
 * Class AuthenticationMiddleware
 * @package MBComponents\MiddleWare
 */
class AuthenticationMiddleware extends Middleware
{
    /**
     * All route path that need to be protected
     * @var array $protectedRoutes
     */
    private $protectedRoutes = [
        '/profile' => ['POST', 'GET'],
        '/registration-completed' => ['GET'],
        '/pre-order/' => ['POST', 'GET'],
        '/request/' => ['POST', 'GET'],
        '/review/' => ['POST', 'GET'],
        '/cancel-request/' => ['GET'],
        '/read/' => ['GET'],
        '/jsScript' => ['GET'],
    ];

    /**
     * Check each called route if it require an authentication
     * if so then return a redirection response or a json response with 400 status
     */
    public function call()
    {
        /** @var Slim $app */
        $app = $this->getApplication();
        /** Get requested route */
        $requestedRoute = $app->sfRequest->getPath();
        /** Get user from session */
        $userSession = $app->session->get('userInfo');
        /** Check if the requested route require authentication */
        if ($this->isPathMatch($requestedRoute, $app->sfRequest->getMethod()) &&
            !(isset($userSession) && !empty($userSession))) {
            /** If it'is a xhr request then return a json response else redirect to home page */
            if ($app->sfRequest->isXmlHttpRequest()) {
                $this->jsonResponse(
                    [
                        'success' => false,
                        'message' => 'You are not logged in , please login first'
                    ],
                    401
                );
            } else {
                $this->app->response()->redirect(
                    $this->app->urlFor(
                        'main',
                        ['reg' => $app->sfRequest->get('region'), 'lang' => $app->sfRequest->get('lang')]
                    )
                );
            }
        } else {
            $this->next->call();
        }
    }

    /**
     * Return slim json response
     * @param $data
     * @param $status
     */
    private function jsonResponse($data, $status)
    {
        $this->getApplication()->response->setBody(json_encode($data));
        $this->getApplication()->response->setStatus($status);
        $this->getApplication()->response->header('Content-Type', 'application/json');
    }

    /**
     * Check if the url is in the protected list
     * @param $url
     * @param $method
     * @return bool
     */
    public function isPathMatch($url, $method)
    {
        foreach ($this->protectedRoutes as $key => $value) {
            if ((strpos($url, $key)!==false || $url==$key)&& in_array($method, $this->protectedRoutes[$key])) {
                return true;
            }
        }
        return false;
    }
}

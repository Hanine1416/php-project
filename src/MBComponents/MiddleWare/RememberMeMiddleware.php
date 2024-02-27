<?php
/**
 * Created by PhpStorm.
 * User: mobelite
 * Date: 27/05/2019
 * Time: 10:23
 */

namespace MBComponents\MiddleWare;

use MBComponents\Helpers\Encryption;
use MBComponents\Services\SLX;
use MBComponents\Slim;
use Slim\Middleware;

/**
 * Login the user from remember me cookie
 *
 * Class RememberMeMiddleware
 * @package MBComponents\MiddleWare
 */
class RememberMeMiddleware extends Middleware
{
    /** @var Slim $app */
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Call
     *
     * Perform actions specific to this middleware and optionally
     * call the next downstream middleware.
     * @throws \Exception
     */
    public function call()
    {
        /** Get user from the session */
        $userSession = $this->app->session->get('userInfo')??null;
        /** If the session has ended and the remember me cookie still persistent then login the user */
        if (!$userSession &&
            $this->app->getCookie('rem_user') &&
            $this->app->request->getMethod()) {
            /**  Decrypt the user id from cookie  */
            $userId = Encryption::decrypt($this->app->getCookie('rem_user'));
            /** @var SLX $slxService */
            $slxService = $this->app->getService(SLX::class);
            $userService = $slxService->getUserService();
            /** Authenticate the user */
            $userService->authenticate($userId);
        }
        $this->next->call();
    }
}

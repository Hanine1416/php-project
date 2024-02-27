<?php
/**
 * Created by PhpStorm.
 * User: mobelite
 * Date: 21/06/2018
 * Time: 12:00
 */

namespace MBComponents\MiddleWare;

use lib\Config;
use MBComponents\Slim;
use Slim\Middleware;

/**
 * This middleware logout use after certain inactivity period
 * and redirect them to home page or return 401 error code
 *
 * Class SessionTimeOutMiddleware
 * @package MBComponents\MiddleWare
 */
class SessionTimeOutMiddleware extends Middleware
{

    /**
     * Call
     *
     * Perform actions specific to this middleware and optionally
     * call the next downstream middleware.
     */
    public function call()
    {
        /** @var Slim $app */
        $app = $this->getApplication();
        $lapse = time() - $app->session->getMetadataBag()->getLastUsed();
        $userSession = $app->session->get('userInfo')??null;
        /** Set session timeout from config */
        $sessionTimeout = Config::read('sessionTimeout');
        /** If the users is logged in and remember me is off and inactivity time passed the authorised time  */
        if (($lapse > $sessionTimeout) && $userSession && !$app->getCookie('rem_user')) {
            /** Logout the user */
            $app->session->remove('userInfo');
            $app->session->remove('user');
            $app->session->remove('adminUser');
            $app->session->getFlashBag()->add('error', 'You have been logged out please login first');
            /** If the request is ajax then return a json response with not authorized code 400 */
            if ($app->sfRequest->isXmlHttpRequest()) {
                $app->response->setBody(json_encode(['success' => false]));
                $app->response->setStatus(401);
                $app->response->header('Content-Type', 'application/json');
            /** Redirect to home page */
            } else {
                $this->app->response()->redirect(
                    $this->app->urlFor(
                        'main',
                        ['reg' => $app->sfRequest->get('region'), 'lang' => $app->sfRequest->get('lang')]
                    )
                );
            }
        } else {
            /** Else continue the normal process  */
            $this->next->call();
        }
    }
}

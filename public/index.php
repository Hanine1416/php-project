<?php
/**
 * This file is part of the Inspection Copy.
 * Created by Mobelite.
 * @author Mobelite <contact@mobelite.Fr>
 * @license For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$loader = require_once '../vendor/autoload.php';

require_once '../config.php';
require_once '../preConfig.php';
use Symfony\Component\Debug\ErrorHandler;
use lib\Config;

ErrorHandler::register();
/** Increase soap call response timeout */
ini_set("default_socket_timeout", 1500);
/** Setup custom Twig view */
$twigView = new \MBComponents\Twig\SlimTwig();
/** Slim app init  $app */
$app = new \MBComponents\Slim(
    [
        'debug' => Config::read('debug'),
        'view' => $twigView,
        'templates.path' => Config::read('templateLocal'),
        'locales.path' => Config::read('langLocal'), // Added i18n //
        'log.level' => \Slim\Log::WARN,
        'log.writer' => new \Slim\Logger\DateTimeFileWriter(
            [
                'path' => LOG_DIRECTORY,
                'name_format' => 'Y-m-d',
                'message_format' => '%label% - %date% - %message%'
            ]
        ),
        'cookies.httponly' => true,
        'cookies.encrypt' => false,
        'cookies.secret_key' => Config::read('secret_key'),
        'cookies.cipher' => MCRYPT_RIJNDAEL_256,
        'cookies.cipher_mode' => MCRYPT_MODE_CBC
    ]
);
/** Add remember me middleware to login user if session ended */
$app->add(new \MBComponents\MiddleWare\RememberMeMiddleware($app));
/** Add authentication middleware to protect route that require authentication */
$app->add(new \MBComponents\MiddleWare\AuthenticationMiddleware());
/** Add session timeout middleware */
$app->add(new \MBComponents\MiddleWare\SessionTimeOutMiddleware());
/** Show error page for prod or dev env */
$app->add(new \MBComponents\MiddleWare\MiddlewareError());

Config::write('app', $app);
Config::write('loader', $loader);
/**
 * Add debug router it should be called first so that the last route in Main.router don't throw an exception
 */
if (Config::read('debug')) {
    $finder = new \Symfony\Component\Finder\Finder();

    /** @var \Symfony\Component\Finder\SplFileInfo $file */
    foreach ($finder->in(__DIR__ . '/../routers_dev') as $file) {
        require_once $file->getPathname();
    }
}

/** Automatically load router files */
$routers = glob('../routers/*.router.php');
foreach ($routers as $router) {
    require_once $router;
}
$app->init()->run();
$app->terminate();

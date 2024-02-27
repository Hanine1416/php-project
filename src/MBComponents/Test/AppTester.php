<?php
/**
 * Created by PhpStorm.
 * User: medNaceur
 * Date: 24/06/2019
 * Time: 12:53
 */

namespace MBComponents\Test;

use lib\Config;
use MBComponents\Slim;
use Slim\Environment;

class AppTester extends \Codeception\Test\Unit
{
    /** @var \MBComponents\Slim */
    protected $app;
    private $basePath;
    const UNIT_TEST_EMAIL = "unit_test@mobelite.fr";

    /** Configure the application initialisation "MOCK" */
    public function __construct(?string $name = null, array $data = array(), string $dataName = '')
    {
        $loader = require __DIR__ . '/../../../vendor/autoload.php';
        parent::__construct($name, $data, $dataName);
        if (session_status() == PHP_SESSION_NONE) {
            Environment::mock(array_merge(array(
                'REQUEST_METHOD' => 'Get',
                'PATH_INFO' => '',
                'SERVER_NAME' => 'slim-test.dev',
            ), []));
            $this->basePath = __DIR__ . '/../../../';
            require_once $this->basePath . 'config.php';
            require_once $this->basePath . 'preConfig.php';
            $app = new Slim();
            Config::write('app', $app);
            Config::write('loader', $loader);
            // Automatically load router files
            $routers = glob($this->basePath . '/routers/*.router.php');
            foreach ($routers as $router) {
                require_once $router;
            }
            $app->init();
            $this->app = $app;
            Config::write('app', $app);
        } else {
            $this->app = Config::read('app');
        }
    }
}

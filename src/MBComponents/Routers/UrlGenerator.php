<?php
/**
 * This file is part of the Inspection Copy.
 * Created by mobelite.
 * Date: 4/13/18
 * Time: 14:46
 * @author: Mobelite <www.mobelite.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MBComponents\Routers;

use lib\Config;
use MBComponents\Exceptions\UnableGenerateRouteException;
use MBComponents\Slim;
use Slim\Route;

/**
 * Class UrlGenerator
 * @package MBComponents\Routers
 */
class UrlGenerator
{
    /** @var Slim $slim */
    private $slim;
    /** @var array $routers */
    private $routers = [];

    /**
     * UrlGenerator constructor.
     * @param Slim $slim
     */
    public function __construct(Slim $slim)
    {
        $this->slim = $slim;
        $this->getRouters();
    }

    /**
     * @param $name
     * @param array $parameters
     * @return string
     * @throws \Exception
     */
    public function generate($name, array $parameters = []):string
    {
        if (!array_key_exists($name, $this->routers)) {
            throw new UnableGenerateRouteException('Unable to generate URL ' . $name);
        }

        /** define default reg and lang from session */
        if (!isset($parameters['lang'])) {
            $parameters['lang'] = $this->slim->session->get('lang');
        }
        if (!isset($parameters['reg'])) {
            $parameters['reg'] = $this->slim->session->get('region');
        }

        /** @var string $route */
        $route = $this->routers[$name];

        /**
         * @var string $key
         * @var string $parameter
         * replace route parameters in pathInfo
         */
        foreach ($parameters as $key => $parameter) {
            $route = str_replace(':' . $key, $parameter, $route);
        }
        return Config::read('path') . $route;
    }

    /**
     * Set all slim routers to a local array with the route path and route name as index
     */
    private function getRouters():void
    {
        $appRouters = $this->slim->router();
        /** @var Route $router */
        foreach ($appRouters->getNamedRoutes() as $router) {
            $this->routers[$router->getName()] = $router->getPattern();
        }
    }
}

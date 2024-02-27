<?php
/**
 * This file is part of the Inspection Copy.
 * Created by mobelite.
 * Date: 3/23/18
 * Time: 16:59
 * @author: Mobelite <www.mobelite.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MBComponents\Twig;

use lib\Config;
use MBComponents\Helpers\Yaml\YamlManipulator;
use MBComponents\Slim;
use MBComponents\Twig\Extensions\AppExtension;
use MBComponents\Twig\Extensions\BookExtension;
use MBComponents\Twig\Extensions\UserExtension;
use Slim\Views\TwigExtension;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;

/**
 * Class TwigEnvironment
 * @package MBComponents\Twig
 */
class TwigEnvironment extends \Twig_Environment
{
    /** @var string */
    private $baseUrl;

    /** @var Slim $app */
    private $app;

    /**
     * TwigEnvironment constructor.
     * @param \Twig_LoaderInterface $loader
     * @param array $options
     */
    public function __construct(\Twig_LoaderInterface $loader, array $options)
    {
        parent::__construct($loader, $options);
        $this->app = Config::read('app');
        $this->init();
    }

    /**
     * Initialize TwigEnvironment global variables & Extensions
     */
    public function init(): void
    {
        /** Add configuration parameters as global vars */
        $this->addGlobal(
            'configParameters',
            YamlManipulator::getFileContents(__DIR__ . '/../config/params.yml')['parameters']
        );
        /** Add request as global Var */
        $this->addGlobal('request', $this->app->sfRequest);
        $slimTwigExtension = new TwigExtension();

        $path = Config::read('path');

        /** Add global variables */
        $this->addGlobal('imageCdn', (Config::read('pathimageCDN') != "") ? Config::read('pathimageCDN') : '');
        $this->addGlobal('currentUrl', $slimTwigExtension->currentUrl());
        $this->addGlobal('originalPath', $path);
        $this->addGlobal('recaptchasite', Config::read('recaptchaPublic'));
        $this->addGlobal('session', $this->app->session);
        $this->addGlobal('app_version', Config::read('app_version'));
        $this->addGlobal('site_languages', Config::read('wsLanguages'));
        $this->addGlobal('site_languages_regions', Config::read('langRegions'));
        $this->addGlobal('months', Config::read('months'));
        $this->addGlobal('lang',$this->app->session->get('site-lang'));
        $this->addGlobal('region',$this->app->session->get('region'));
        $this->addGlobal('cookies',$this->app->sfRequest->cookies);
        $this->addGlobal('app', $this->app);
        if (Config::read('debug')) {
            $this->addGlobal('debug', true);
        }
        $this->baseUrl = $path;

        $this->addGlobal('basePath', $this->baseUrl);

        $this->addExtension($slimTwigExtension);

        /** Add asset extensions */
        $version = 'v=1.0';
        if (Config::has('app_version')) {
            $version = 'v=' . Config::read('app_version');
        }
        $versionStrategy = new StaticVersionStrategy($version);
        $defaultPackage = new Package($versionStrategy);
        $packages = new Packages($defaultPackage, []);
        $assetExtension = new AssetExtension($packages);
        $this->addExtension($assetExtension);
        $this->addExtension(new AppExtension($this->app));
        $this->addExtension(new UserExtension($this->app));
        $this->addExtension(new BookExtension($this->app));
        /** If debug mode enabled then add debug extensions */
        if ($this->debug) {
            $debugExtension = new \Twig_Extension_Debug();
            $this->addExtension($debugExtension);
            $profile = new \Twig_Profiler_Profile();
            $this->addExtension(new \Twig_Extension_Profiler($profile));
        }
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @param array $parameters
     * @return string
     */
    public function updateBaseUrl($parameters = []): string
    {
        $this->baseUrl = $this->app->sfRequest->getSchemeAndHttpHost() . $this->app->sfRequest->getBaseUrl() . '/';
        return $this->baseUrl;
    }


    /**
     * Render twig template
     * @param $view
     * @param array $parameters
     * @return string The rendered template
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function renderView($view, array $parameters = []): string
    {
        return $this->render($view, $parameters);
    }
}

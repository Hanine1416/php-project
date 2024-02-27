<?php
/**
 * This file is part of the Inspection Copy.
 * Created by mobelite.
 * Date: 3/26/18
 * Time: 13:11
 * @author: Mobelite <www.mobelite.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MBComponents;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\EntityManager;
use lib\Config;
use MainBundle\MainBundle;
use MBComponents\Doctrine\DoctrineConfig;
use MBComponents\Form\FormSetup;
use MBComponents\Helpers\Mailer;
use MBComponents\HttpFoundation\Request;
use MBComponents\HttpFoundation\Session;
use MBComponents\Monolog\Monolog;
use MBComponents\Monolog\MonologInterface;
use MBComponents\Routers\UrlGenerator;
use MBComponents\Services\AppService;
use MBComponents\Services\GoogleReCaptcha;
use MBComponents\Services\SLX;
use MBComponents\Twig\TwigEnvironment;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Translation\Formatter\MessageFormatter;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;
use UserBundle\UserBundle;

/**
 * Class Slim
 * @package MBComponents
 */
class Slim extends \Slim\Slim
{
    /** @var Request */
    public $sfRequest;
    /** @var Session */
    public $session;
    /** @var TwigEnvironment */
    public $sfTwig;
    /** @var Translator */
    public $sfTranslator;
    /** @var FormFactory */
    public $sfFormFactory;
    /** @var EntityManager $sfEntityManager */
    public $sfEntityManager;
    /** @var Mailer $mailer */
    public $mailer;
    /** @var  UrlGenerator $urlGenerator */
    private $urlGenerator;
    /** @var array $bundles */
    private $bundles = [];
    /** @var MonologInterface $monolog */
    private $monoLog;
    /** @var Container */
    public $sfContainer;
    /** @var DebugStack */
    private $doctrineStack;

    /**
     * Init the application by setting all dependencies
     * @return $this
     * @throws \Doctrine\ORM\ORMException
     */
    public function init(): Slim
    {
        AnnotationRegistry::registerLoader(array(Config::read('loader'), 'loadClass'));
        $request = Request::createFromGlobals();
        $this->session = new Session();

        /** Init the default web site language */
        $this->session->set('default-lang', $this->getCookie('default-lang') ?? $request->attributes->get('lang'));

        /** Init the default web site language from switcher */
        $this->session->set('site-lang', $this->getCookie('site-lang') ?? $this->getCookie('lang') ?? "en");

        /** Init the switch-catalog if user change the default catalogue*/
        $this->session->set('switch-catalog', $this->getCookie('switch-catalog'));

        /** Init lang & countryCode on session */
        $this->session->set('lang', $this->getCookie('lang') ?? 'en');
        $this->session->set('region', $this->getCookie('region') ?? '7');

        /** Save lang & region into cookie so if session expire we can get last region & lang */
        $this->setCookie('lang', $this->session->get('lang'));
        $this->setCookie('reg', $this->session->get('region'));

        /** Save site langue */
        $this->session->set('site-lang', $this->getCookie('site-lang') ?? $this->getCookie('site-lang') ?? $this->session->get('lang'));

        $this->sfRequest = $request;

        /** Init twig & translator */
        $this->initTranslator();
        /** init bundles */
        $this->bundles = [
            new  MainBundle($this),
            new  UserBundle($this),
        ];
        $this->initTwig();

        /** init form Factory */
        $formSetup = new FormSetup();
        $this->sfFormFactory = $formSetup->getFormFactory($this->sfTwig, $this->sfTranslator);

        /** init Doctrine EntityManager */
        $doctrineConfig = new DoctrineConfig(Config::read('debug'));
        $this->sfEntityManager = $doctrineConfig->getEntityManager();
        $this->doctrineStack = $doctrineConfig->getStack();


        /** init Mailer */
        $this->mailer = new Mailer();

        /** merge slim twig and custom twig extensions */
        $this->view()->parserExtensions = $this->sfTwig->getExtensions();

        $this->initContainer();

        return $this;
    }

    /**
     * Init the translator
     * @return $this
     */
    public function initTranslator(): Slim
    {
        $locale   = $this->getCookie('site-lang') ?? $this->session->get('lang');
        $cacheDir = CACHE_DIRECTORY . '/translations';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        /** @var Translator $translator */
        $translator = new Translator(
            $locale,
            new MessageFormatter(),
            $cacheDir,
            $this->settings['debug']
        );
        /** add MbComponents\translations files to translator component */
        $translator->addLoader('yml', new YamlFileLoader());
        foreach (Config::read('languages') as $value) {
            $messageFile = TRANSLATIONS_DIRECTORY .'messages.'. $value . '.yml';
            if (file_exists($messageFile)) {
                $translator->addResource('yml', $messageFile, $value);
            }
        }
        $this->sfTranslator = $translator;
        return $this;
    }

    /**
     * Init twig engine for each bundle
     * Load each bundle views
     * Configure cache folder
     * Add translator extension
     * @return $this
     */
    public function initTwig(): Slim
    {
        /**
         * Init twig
         */
        $loader = new \Twig_Loader_Filesystem([
            TEMPLATE_DIRECTORY,
            __DIR__ . '/../../vendor/symfony/twig-bridge/Resources/views/Form',
        ]);

        /**
         * Load bundles Resources views
         */
        foreach ($this->bundles as $bundle) {
            $loader->setPaths($bundle->getPath() . '/Resources/views', $bundle->getName());
        }
        /** @var string $cacheDir */
        $cacheDir = CACHE_DIRECTORY . '/twig';
        if (!is_dir($cacheDir)) {
           // mkdir($cacheDir, 0777, true);
           // chmod($cacheDir, 0777);
        }
        $this->sfTwig = new TwigEnvironment($loader, [
            'cache' => false,
            'debug' => true
        ]);
        /** Add translation extension to twig */
        $translationExtension = new TranslationExtension($this->sfTranslator);
        $this->sfTwig->addExtension($translationExtension);

        return $this;
    }

    /**
     * Return all url generator
     * @return UrlGenerator
     */
    public function getUrlGenerator(): UrlGenerator
    {
        if (null === $this->urlGenerator) {
            $this->urlGenerator = new UrlGenerator($this);
        }
        return $this->urlGenerator;
    }

    /**
     * @return Monolog
     */
    public function getMonoLog(): Monolog
    {
        if (!$this->monoLog) {
            $this->monoLog = new Monolog($this->sfRequest);
        }
        return $this->monoLog;
    }

    /**
     * Work With Symfony Container
     */
    /** inject all services into the container */
    private function initContainer(): void
    {
        $this->sfContainer = new Container();
        $this->sfContainer->set(Session::class, $this->session);
        $this->sfContainer->set(SLX::class, new SLX($this->sfContainer));
        $this->sfContainer->set(TwigEnvironment::class, $this->sfTwig);
        $this->sfContainer->set(EntityManager::class, $this->sfEntityManager);
        $this->sfContainer->set(Translator::class, $this->sfTranslator);
        $this->sfContainer->set(Mailer::class, $this->mailer);
        $this->sfContainer->set(Request::class, $this->sfRequest);
        $this->sfContainer->set(AppService::class, new AppService($this));
        $this->sfContainer->set(GoogleReCaptcha::class, new GoogleReCaptcha());
        $this->sfContainer->set(Monolog::class, $this->getMonoLog());
        $this->sfContainer->setParameter('lang', $this->session->get('lang'));
        $this->sfContainer->setParameter('region', $this->session->get('region'));
    }

    /**
     * @param \Exception $e
     * @return mixed
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function defaultError($e)
    {
        $logger = $this->getMonoLog();
        $debug = $this->settings['debug'];

        /** @var string $environment */
        $environment = $debug ? '_dev' : '_prod';

        /** write to log */
        $logger->log('crit', $environment, $e->getCode(), ['message' => $e->getMessage()]);

        echo $this->sfTwig->render('exceptions/error.html.twig', [
            'exception' => $e
        ]);
    }

    /**
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->sfContainer;
    }

    /**
     * return service from the container
     * @param string $class
     * @return object
     * @throws \Exception
     */
    public function getService(string $class)
    {
        return $this->sfContainer->get($class);
    }

    /**
     * This Function allow you to do whatever you want after Slim Run Application
     */
    public function terminate(): void
    {
        /**
         * Log SQL Queries to _dev file
         * Debug Doctrine
         */
        if ($this->settings['debug'] && count($this->doctrineStack->queries)) {
            foreach ($this->doctrineStack->queries as $query) {
                $this->getMonoLog()->log('info', '_dev', 'Doctrine', [
                    'query' => $query['sql'],
                    'params' => $query['params'],
                    'executionTime' => $query['executionMS']
                ]);
            }
        }
    }
}

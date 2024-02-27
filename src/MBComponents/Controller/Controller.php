<?php
/**
 * This file is part of the Inspection Copy.
 * Created by mobelite.
 * Date: 3/23/18
 * Time: 17:31
 * @author: Mobelite <www.mobelite.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MBComponents\Controller;

use Doctrine\ORM\EntityManager;
use Exception;
use lib\Config;
use MainBundle\Entity\Cover;
use MBComponents\Exceptions\NotFoundException;
use MBComponents\Helpers\Encryption;
use MBComponents\Helpers\MainHelper;
use MBComponents\HttpFoundation\Request;
use MBComponents\HttpFoundation\Session;
use MBComponents\Services\AppService;
use MBComponents\Services\SLX;
use MBComponents\Slim;
use MBComponents\Twig\TwigEnvironment;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;
use UserBundle\Entity\Code;
use UserBundle\Entity\User;

/**
 * Class Controller
 * @package MBComponents\Controller
 */
abstract class Controller {
    /** @var Slim $app */
    private $app;

    /**
     * @var ContainerInterface $container
     */
    protected $container;
    /**
     * @var string
     */
    protected $language;
    /**
     * @var string
     */
    protected $region;
    /**
     * @var array
     */
    protected $categories;

    /**
     * Controller constructor.
     * @param bool $isAdmin
     * @throws Exception
     */
    public function __construct(bool $isAdmin = false) {
        $this->generateRobots();
        $params = $this->getApp()->router->getCurrentRoute()->getParams();
        if ($this->app->getCookie('lang') !== null && $this->app->getCookie('lang') !== "undefined"){
            $language = $this->app->getCookie('lang');
        } else {
            $language = 'en';
        }
        if ($this->app->getCookie('region') !== null && $this->app->getCookie('region') !== "undefined") {
            $region = $this->app->getCookie('region');
        } else {
            $region = '7';
        }
           $defaultRegion= $region ;

        $search = strpos($language, '?');
        if ($language === 'en' && (int)$region===1 )
        {
            $this->getApp()->sfTranslator->setLocale('anz');
        }
        if ($search)
        {
            $language = substr($language, 0, $search);
        }
        $language = strtolower($language);

        $this->getApp()->session->set('lang', $language);
        $this->getApp()->session->set('region', $region);

        $this->language = $language;
        $this->region =   $region;
        /** Select the current site id for the key language to get all country according to the language*/
        /** (used to call salesLogix WS elsGet_PickList) */
        $region==1 ? $siteKey ="anz" : ($siteKey = $language??'old');
        Config::write(
            'currentSiteId',
            array_key_exists(
                $siteKey,
                Config::read('siteIds')
            ) ?  Config::read('siteIds')[$siteKey] : Config::read('siteIds')['old']
        );

        if (!$isAdmin)
        {
            $this->container = $this->getApp()->container;
            $switchCatalog   = $this->getApp()->session->get('switch-catalog');
            /** check if the user is logged in then redirect to his code region */
            $userService = $this->getSlx()->getUserService();
            if ($this->isLoggedIn() && $this->getUser()->getCountry())
            {
                $data = $userService->getLanguageRegionFromCountry($this->getUser()->getCountry());
                $userRegion = $data['region'];
                $userLang = $data['language'];
                $userCountry =  strtolower(trim($this->getUser()->getCountry()));
            } else
            {
                /** get user country info from his ip then redirect him to the right language & region */
                $ipAddress = MainHelper::getClientIpAddress($this->getRequest()->server->all());
                if (!$countryCode = $this->getSession()->get('countryCode-'.$ipAddress))
                {
                    $countryInfo =  $this->getIpInfo($ipAddress);
                    $countryCode = $countryInfo['countryCode'];
                    $this->getSession()->set('countryCode-'.$ipAddress, $countryCode);
                }
                $data = $userService->getLanguageRegionFromCountry($countryCode);
                $userRegion = $data['region'];
                $userLang = $data['language'];
                $userCountry = $data['country'];
            }

            /** Get catalog switcher from cookies and update session params from this new switcher **/
            if (!empty($switchCatalog))
            {
                $tabSwitcher = explode('/', $this->app->getCookie('switch-catalog'));
                $userRegion  = $tabSwitcher[1];
                $this->getApp()->session->set('region', $userRegion);
                $this->getApp()->session->set('lang', $tabSwitcher[2]);
            }

            $this->app->setCookie('lang', $userLang);
            $this->app->setCookie('region', $userRegion);
            $this->app->setCookie('country', $userCountry);

            /** Add categories lang and region to twig global */
            $this->setCategories($this->getSlx()->getBookService()->getCategories());
            $this->getApp()->sfTwig->addGlobal('categories', $this->getCategories());
            $this->container->get("view")->getEnvironment()->addGlobal('lang', $userLang);
            $this->container->get("view")->getEnvironment()->addGlobal('region', $userRegion);
            /** Refresh the page to accept new params when session data different from user data*/
            if ($userRegion != $this->getApp()->session->get('region'))
            {
                $this->getApp()->session->set('lang', $userLang);
                $this->getApp()->session->set('region', $userRegion);
                header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
                header("Refresh:0");
            }

            //delete admin cookies
            $this->app->setCookie("admin_lang", $this->getApp()->session->get('admin_lang'), time() - 3600, "/");
            $this->app->setCookie("admin_region", $this->getApp()->session->get('admin_region'), time() - 3600, "/");

        } else
        {
            /**  set the admin session lang and region*/
            $userService = $this->getSlx()->getUserService();
            $countryInfo =  $this->getIpInfo(MainHelper::getClientIpAddress($this->getRequest()->server->all()));
            $data        = $userService->getLanguageRegionFromCountry($countryInfo['countryCode']);
            $userRegion  = $data['region'];
            $userLang   = $data['language'];
            $this->app->setCookie('lang', $userLang);
            $this->app->setCookie('reg', $userRegion);
        }
        $this->language = $language = $this->getApp()->session->get('lang');
        $this->region   = $this->getApp()->session->get('region');
        /** Select the current site id that depend from the selected language (used to call salesLogix WS) */
        $region==1 ? $siteKey ="anz" : ($siteKey = $language??'old');
        Config::write(
            'currentSiteId',
            array_key_exists(
                $siteKey,
                Config::read('siteIds')
            ) ?  Config::read('siteIds')[$siteKey] : Config::read('siteIds')['old']
        );

    }

    /**/
    public function generateRobots(){
        $env = Config::read('environment');
        $filerobots = 'robots.txt';
        file_put_contents($filerobots, '');
        if (file_exists($filerobots))
        {
            if ($env != "prod"){
                $robotsTxtContent = "User-agent: *\n";
                $robotsTxtContent .= "Disallow: /";
            }
            else{
                $robotsTxtContent = "User-agent: *\n";
                $robotsTxtContent .= "Disallow: /registration-completed\n";
                $robotsTxtContent .= "Disallow: /profile/\n";
                $robotsTxtContent .= "Disallow: /user/\n";
                $robotsTxtContent .= "Disallow: /admin/\n";
                $robotsTxtContent .= "Disallow: /register\n";
                $robotsTxtContent .= "Disallow: /0/en/account\n";
                $robotsTxtContent .= "Disallow: /1/en/account\n";
                $robotsTxtContent .= "Disallow: /2/en/account\n";
                $robotsTxtContent .= "Disallow: /3/en/account\n";
                $robotsTxtContent .= "Disallow: /4/en/account\n";
                $robotsTxtContent .= "Disallow: /5/en/account\n";
                $robotsTxtContent .= "Disallow: /6/en/account\n";
                $robotsTxtContent .= "Disallow: /7/en/account\n";
                $robotsTxtContent .= "Disallow: /8/en/account\n";
                $robotsTxtContent .= "Disallow: /9/en/account\n";
                $robotsTxtContent .= "User-agent: GPTBot\n";
                $robotsTxtContent .= "Disallow: /\n";
                $robotsTxtContent .= "User-agent: ChatGPT-User\n";
                $robotsTxtContent .= "Disallow: /\n";
                $robotsTxtContent .= "User-agent: Google-Extended\n";
                $robotsTxtContent .= "Disallow: /\n";
                $robotsTxtContent .= "Sitemap: https://inspectioncopy.elsevier.com/sitemap.xml";
            }
            $fileHandle = file_put_contents($filerobots, $robotsTxtContent);
        }
    }
    /**
     * @return Slim
     */
    public function getApp(): Slim {
        if (null === $this->app)
        {
            $this->app = Config::read('app');
        }
        return $this->app;
    }

    /**
     * @return TwigEnvironment
     */
    public function getTwig(): TwigEnvironment {
        return $this->getApp()->sfTwig;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request {
        return $this->getApp()->sfRequest;
    }

    /**
     * @return Session
     */
    public function getSession(): Session {
        return $this->getApp()->session;
    }

    /**
     * @return Translator
     */
    public function getTranslator(): Translator {
        return $this->getApp()->sfTranslator;
    }

    /**
     * @return array
     */
    public function getCategories(): ?array {
        return $this->categories;
    }

    /**
     * @param array $categories
     */
    public function setCategories(array $categories): void {
        $this->categories = $categories;
    }

    /**
     * @param $view
     * @param array $parameters
     * @param Response|null $response
     * @return \Slim\Http\Response
     * @throws Exception
     */
    protected function render($view, array $parameters = [], Response $response = null): \Slim\Http\Response {
        /** @var string $content */
        $content = $this->renderView($view, $parameters);
        if (null === $response)
        {
            $response = new Response($content, Response::HTTP_OK, array('content-type' => 'text/html'));
        }
        $this->app->response->headers->set('content-type', ' text/html; charset=utf-8');
        $this->app->response->setBody($response->getContent());
        return $this->app->response;
    }


    /**
     * alias to render twig template
     * @param $view
     * @param array $parameters
     * @return string
     * @throws Exception
     */
    public function renderView($view, array $parameters = []): string {
        return $this->app->sfTwig->renderView($view, $parameters);
    }

    /**
     * alias to add flash messages
     * @param $type
     * @param $message
     * @throws Exception
     */
    public function addFlash($type, $message): void {
        /** @var FlashBagInterface $flashBag */
        $flashBag = $this->app->session->getFlashBag();
        $flashBag->add($type, $message);
    }

    /**
     * @param $data
     * @param int $status
     * @param array $headers
     * @return string
     */
    protected function json($data, $status = 200, $headers = []): string {
        $response = new JsonResponse($data, $status, $headers);
        return $response->getContent();
    }

    /**
     * @param $data
     * @param int $status
     * @param array $headers
     * @return \Slim\Http\Response
     */
    protected function renderJson($data, $status = 200, $headers = []): \Slim\Http\Response {
        $response = new JsonResponse($data, $status, $headers);
        $this->app->response->headers->set('Content-Type', 'application/json');
        $this->app->response->setBody($response->getContent());
        return $this->app->response;
    }


    /**
     * @param $type
     * @param null $data
     * @param array $options
     * @return FormInterface
     */
    protected function createForm($type, $data = null, array $options = []): FormInterface {
        return $this->getApp()->sfFormFactory->create($type, $data, $options);
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager(): EntityManager {
        return $this->getApp()->sfEntityManager;
    }

    /**
     * @param $id
     * @param array $parameters
     * @param string $domain
     * @param null $local
     * @return string
     */
    public function trans($id, array $parameters = [], $domain = 'messages', $local = null): string {
        return $this->getApp()->sfTranslator->trans($id, $parameters, $domain, $local);
    }

    /**
     * @param $toValidate
     * @param array $validators
     * @return ConstraintViolationListInterface
     */
    protected function validate($toValidate, array $validators = []): ConstraintViolationListInterface {
        $validator = Validation::createValidator();
        return $validator->validate($toValidate, $validators);
    }

    /**
     * @param $url
     * @throws Exception
     */
    protected function redirect($url): void {
        $this->app->redirect($url);
    }

    /**
     * @return SLX|object
     * @throws Exception
     */
    public function getSlx() {
        return $this->app->getService(SLX::class);
    }

    /**
     * alias to appService isLoggedIn
     * @return bool
     * @throws Exception
     */
    public function isLoggedIn(): bool {
        /** @var AppService $appService */
        $appService = $this->app->getService(AppService::class);
        return $appService->isLoggedIn();
    }

    /**
     * alias to appService getUser
     * @return User
     * @throws Exception
     */
    public function getUser(): ?User {
        /** @var AppService $appService */
        $appService = $this->app->getService(AppService::class);
        return $appService->getUser();
    }

    /**
     * alias to appService generateUrl
     * @param string $name
     * @param array $parameters
     * @return string
     * @throws Exception
     */
    public function generateUrl(string $name, array $parameters = []): ?string {
        /** @var AppService $appService */
        $appService = $this->app->getService(AppService::class);
        return $appService->generateUrl($name, $parameters);
    }


    /**
     * Fix country name for brazil & spain
     * @param $country
     * @return string
     */
    protected function fixCountryName($country): ?string {
        if (strtoupper($country) === 'BRASIL')
        {
            $country = 'Brazil';
        } else if ($country === 'España')
        {
            $country = 'Spain';
        }
        return $country;
    }

    /**
     * return the country for the user ip address
     * @param string $ip
     * @return array
     */
    public function getIpInfo(string $ip): array {
        $serverHttpAgent = filter_input(INPUT_SERVER,'HTTP_USER_AGENT');
        /** Return united kingdom country as default */
        $output = ['countryCode'=>'UK'];
        if (! $this->isBot($serverHttpAgent))
        {
            try
            {
                $ipData = MainHelper::getData("https://geoip.maxmind.com/geoip/v2.1/country/".$ip."?pretty", true);
            } catch (\Exception $exception)
            {
                return $output;
            }
            if($ipData['country']){
                if (strlen(trim($ipData['country']['iso_code'])) === 2)
                {
                    $output = [
                        'countryName' => $ipData['country']['names']['en'],
                        'countryCode' => $ipData['country']['iso_code']
                    ];
                }
            }
        }
        return $output;
    }

    /**
     * test if the given user agent was a bot
     * @param $userAgent
     * @return false|int
     */
    public function isBot($userAgent) {

        $botRegexPattern = "(googlebot\/|Googlebot\-Mobile|Googlebot\-Image|Google favicon|Mediapartners\-Google|
        bingbot|slurp|java|wget|curl|Commons\-HttpClient|Python\-urllib|libwww|httpunit|nutch|phpcrawl|msnbot|
        jyxobot|FAST\-WebCrawler|FAST Enterprise Crawler|biglotron|teoma|convera|seekbot|gigablast|exabot|ngbot|
        ia_archiver|GingerCrawler|webmon |httrack|webcrawler|grub\.org|UsineNouvelleCrawler|antibot|
        netresearchserver|speedy|fluffy|bibnum\.bnf|findlink|msrbot|panscient|yacybot|AISearchBot|
        IOI|ips\-agent|tagoobot|MJ12bot|dotbot|woriobot|yanga|buzzbot|mlbot|yandexbot|purebot|
        Linguee Bot|Voyager|CyberPatrol|voilabot|baiduspider|citeseerxbot|spbot|twengabot|postrank|
        turnitinbot|scribdbot|page2rss|sitebot|linkdex|Adidxbot|blekkobot|ezooms|dotbot|Mail\.RU_Bot|
        discobot|heritrix|findthatfile|europarchive\.org|NerdByNature\.Bot|sistrix crawler|ahrefsbot|
        Aboundex|domaincrawler|wbsearchbot|summify|ccbot|edisterbot|seznambot|ec2linkfinder|gslfbot|
        aihitbot|intelium_bot|facebookexternalhit|yeti|RetrevoPageAnalyzer|lb\-spider|sogou|lssbot|
        careerbot|wotbox|wocbot|ichiro|DuckDuckBot|lssrocketcrawler|drupact|webcompanycrawler|acoonbot|
        openindexspider|gnam gnam spider|web\-archive\-net\.com\.bot|backlinkcrawler|coccoc|integromedb|
        content crawler spider|toplistbot|seokicks\-robot|it2media\-domain\-crawler|ip\-web\-crawler\.com|
        siteexplorer\.info|elisabot|proximic|changedetection|blexbot|arabot|WeSEE:Search|niki\-bot|
        CrystalSemanticsBot|rogerbot|360Spider|psbot|InterfaxScanBot|Lipperhey SEO Service|CC Metadata Scaper|
        g00g1e\.net|GrapeshotCrawler|urlappendbot|brainobot|fr\-crawler|binlar|SimpleCrawler|Livelapbot|
        Twitterbot|cXensebot|smtbot|bnf\.fr_bot|A6\-Indexer|ADmantX|Facebot|Twitterbot|OrangeBot|memorybot|
        AdvBot|MegaIndex|SemanticScholarBot|ltx71|nerdybot|xovibot|BUbiNG|Qwantify|archive\.org_bot|
        Applebot|TweetmemeBot|crawler4j|findxbot|SemrushBot|yoozBot|lipperhey|y!j\-asr|
        Domain Re\-Animator Bot|AddThis|YisouSpider|BLEXBot|YandexBot|SurdotlyBot|AwarioRssBot|
        FeedlyBot|Barkrowler|Gluten Free Crawler|Cliqzbot)";

        return preg_match("/{$botRegexPattern}/", $userAgent);
    }

    /**
     * use this function to redirect logged in user to the correct page
     * @throws Exception
     */
    public function redirectFirstPage()
    {
        //redirect user to put code
      /* if($this->getUser()) {
            $userCode = $this->getEntityManager()->getRepository(Code::class)->findOneBy(['email' =>$this->getUser()->getEmail()]);
            if ($userCode != null ) {
                $this->redirect(
                    $this->generateUrl('confirm-authentication')
                );
            }
            //redirect user to choose interests
            if(!$this->getUser()->getHasInterests()) {
                //redirect user to page of use categories
                $this->redirect(
                    $this->generateUrl('registration-completed', ['lang' => $this->language, 'reg' => $this->region])
                );
            }
        }*/
    }

    /*
     * Clean Data
     */
    function cleanMe($input) {
        $input = str_replace("'", "\'",$input );
        $input = htmlspecialchars($input, ENT_IGNORE, 'utf-8');
        $input = strip_tags($input);
        $input = stripslashes($input);
        return $input;
    }

    /**
     * detect user device, ios and browser
     */
    public function detectDevice() {
        //Detect special conditions devices
        $iPod    = stripos($_SERVER['HTTP_USER_AGENT'],"iPod");
        $iPhone  = stripos($_SERVER['HTTP_USER_AGENT'],"iPhone");
        $iPad    = stripos($_SERVER['HTTP_USER_AGENT'],"iPad");
        $Android = stripos($_SERVER['HTTP_USER_AGENT'],"Android");
        $webOS   = stripos($_SERVER['HTTP_USER_AGENT'],"webOS");
        $deviceData = 'Computer';
        if( $iPod || $iPhone ){
            $deviceData = 'Mobile iPhone';
        }else if($iPad){
            $deviceData = 'Mobile iPad';
        }else if($Android){
            $deviceData = 'Mobile Android';
        }else if($webOS){
            $deviceData = 'Computer webOS';
        }
        $arr_browsers = ["Opera", "Edg", "Chrome", "Safari", "Firefox", "MSIE", "Trident"];
        $agent = $_SERVER['HTTP_USER_AGENT'];
        $user_browser = '';
        foreach ($arr_browsers as $browser) {
            if (strpos($agent, $browser) !== false) {
                $user_browser = $browser;
                break;
            }
        }
        switch ($user_browser) {
            case 'MSIE':
                $user_browser = 'Internet Explorer';
                break;
            case 'Trident':
                $user_browser = 'Internet Explorer';
                break;
            case 'Edg':
                $user_browser = 'Microsoft Edge';
                break;
        }

        $deviceData .=' '.$this->getOS();
        if($user_browser) {
            $deviceData .=' '. $user_browser." browser";
        }
        return $deviceData;
    }

    function getOS() {

        $user_agent = $_SERVER['HTTP_USER_AGENT'];

        $os_platform  = "Unknown OS Platform";

        $os_array     = array(
            '/windows nt 10/i'      =>  'Windows 10',
            '/windows nt 6.3/i'     =>  'Windows 8.1',
            '/windows nt 6.2/i'     =>  'Windows 8',
            '/windows nt 6.1/i'     =>  'Windows 7',
            '/windows nt 6.0/i'     =>  'Windows Vista',
            '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'     =>  'Windows XP',
            '/windows xp/i'         =>  'Windows XP',
            '/windows nt 5.0/i'     =>  'Windows 2000',
            '/windows me/i'         =>  'Windows ME',
            '/win98/i'              =>  'Windows 98',
            '/win95/i'              =>  'Windows 95',
            '/win16/i'              =>  'Windows 3.11',
            '/macintosh|mac os x/i' =>  'Mac OS X',
            '/mac_powerpc/i'        =>  'Mac OS 9',
            '/linux/i'              =>  'Linux',
            '/ubuntu/i'             =>  'Ubuntu',
            '/iphone/i'             =>  'iPhone',
            '/ipod/i'               =>  'iPod',
            '/ipad/i'               =>  'iPad',
            '/android/i'            =>  'Android',
            '/blackberry/i'         =>  'BlackBerry',
            '/webos/i'              =>  'Mobile'
        );

        foreach ($os_array as $regex => $value)
            if (preg_match($regex, $user_agent))
                $os_platform = $value;

        return $os_platform;
    }
}

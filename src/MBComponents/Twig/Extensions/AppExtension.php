<?php
/**
 * This file is part of the Inspection Copy.
 * Date: 3/23/18
 * Time: 16:10
 * @author: Mobelite <www.mobelite.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MBComponents\Twig\Extensions;

use lib\Config;
use MBComponents\Services\SLX;
use MBComponents\Slim;
use Twig\Extension\AbstractExtension;
use Twig_Environment;
use Twig_Template;
use UserBundle\Entity\Code;
use UserBundle\Entity\RequestRegister;
use UserBundle\Entity\User;

/**
 * Class AppExtension
 * @package MBComponents\Twig\Extensions
 */
class AppExtension extends AbstractExtension
{
    /** @var Slim $slim */
    protected $slim;

    /**
     * AppExtension constructor.
     * @param Slim $slim
     */
    public function __construct(Slim $slim)
    {
        $this->slim = $slim;
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        $isDumpOutputHtmlSafe = extension_loaded('xdebug');
        return [
            new \Twig_SimpleFunction('is_logged_in', [$this, 'isLoggedIn']),
            new \Twig_SimpleFunction('array_to_string', [$this, 'arrayToString']),
            new \Twig_SimpleFunction('ip_date', [$this, 'getIpDate']),
            new \Twig_SimpleFunction('site_lang', [$this, 'getSiteLang']),
            new \Twig_SimpleFunction('url', [$this, 'generateUrl']),
            new \Twig_SimpleFunction('getUser', [$this, 'getLoggedInUser']),
            new \Twig_SimpleFunction('getEnv', [$this, 'getEnviroment']),
            new \Twig_SimpleFunction('asset', [$this, 'overrideAsset']),
            new \Twig_SimpleFunction('isInteger', array($this, 'isInteger')),
            new \Twig_SimpleFunction('getBannersByRegion', [$this, 'getBannersByRegion']),
            new \Twig_SimpleFunction(
                'dumper',
                [$this, 'overrideDump'],
                [
                    'is_safe' => $isDumpOutputHtmlSafe ? array('html') : array(),
                    'needs_context' => true,
                    'needs_environment' => true
                ]
            ),
            new \Twig_SimpleFunction('checkStudentResources', [$this, 'checkStudentResources']),
            new \Twig_SimpleFunction('checkInstructorResources', [$this, 'checkInstructorResources']),
        ];
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return [
            new \Twig_SimpleFilter('string', [$this, 'formatString']),
            new \Twig_SimpleFilter('dynamicReplace', [$this, 'dynamicReplace']),
            new \Twig_SimpleFilter('json_decode', [$this, 'jsonDecode']),
            new \Twig_SimpleFilter('site_date_format', [$this, 'langSiteDateFormat']),
            new \Twig_SimpleFilter('fix_link_fr', [$this, 'fixCoverLinkFr']),
            new \Twig_SimpleFilter('int', function ($value) {
                return (int)$value;
            }),
            new \Twig_SimpleFilter('sortbyfield', array($this, 'sortByFieldFilter'))
        ];
    }

    /**
     * Transform array to string
     * @param array $array
     * @return string
     */
    public function arrayToString(array $array): string
    {
        $output = '';

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $output .= $this->arrayToString($value);
            } else {
                $output .= sprintf('<p>%s: %s</p>', $key, $value);
            }
        }

        return $output;
    }

    /**
     * Return loggedIn user
     * @return User|null
     */
    public function getLoggedInUser(): ?User
    {
        return $this->isLoggedIn() ? unserialize($this->slim->session->get('user')) : null;
    }

    /**
     * Generate url form url name
     * @param string $name
     * @param array $parameters
     * @return string
     * @throws \Exception
     */
    public function generateUrl(string $name, array $parameters = []): string
    {
        return $this->slim->getUrlGenerator()->generate($name, $parameters);
    }

    /**
     * Check if user logged in
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        $session = $this->slim->session;
        return $session->has('userInfo');
    }

    /**
     * Get ip and date of request
     * @return string
     */
    public function getIpDate(): string
    {
        /**  */
        $ip = $this->getClientIpServer();
        /** @var \DateTime $currentDate */
        $currentDate = new \DateTime('now');
        /** set UTC as timezone */
        $currentDate->setTimezone(new \DateTimeZone('UTC'));
        /** @var RequestRegister $registerRequest */
        $dateTime = $currentDate->format('Y-m-d') . 'T' . $currentDate->format('H:i:s') . 'Z';
        return $this->trans('email.ip_date', array('%ip%' => $ip, '%date%' => $dateTime));
    }

    /**
     * Function to get the client ip address
     * @return string
     */
    public function getClientIpServer(): string
    {
        $server = $this->slim->sfRequest->server;
        if ($server->has('HTTP_CLIENT_IP') && !empty($server->get('HTTP_CLIENT_IP'))) {
            $ipAddress = $server->get('HTTP_CLIENT_IP');
        } elseif ($server->has('HTTP_X_FORWARDED_FOR') && !empty($server->get('HTTP_X_FORWARDED_FOR'))) {
            $ipAddress = $server->get('HTTP_X_FORWARDED_FOR');
        } elseif ($server->has('HTTP_X_FORWARDED') && !empty($server->get('HTTP_X_FORWARDED'))) {
            $ipAddress = $server->get('HTTP_X_FORWARDED');
        } elseif ($server->has('HTTP_FORWARDED_FOR') && !empty($server->get('HTTP_FORWARDED_FOR'))) {
            $ipAddress = $server->get('HTTP_FORWARDED_FOR');
        } elseif ($server->has('HTTP_FORWARDED') && !empty($server->get('HTTP_FORWARDED'))) {
            $ipAddress = $server->get('HTTP_FORWARDED');
        } elseif ($server->has('REMOTE_ADDR') && !empty($server->get('REMOTE_ADDR'))) {
            $ipAddress = $server->get('REMOTE_ADDR');
        } else {
            $ipAddress = 'UNKNOWN';
        }

        return $ipAddress;
    }

    /**
     * Add translator to twig
     * @param string $id
     * @param array $parameters
     * @param string $domain
     * @param null $locale
     * @return string
     */
    protected function trans(string $id, array $parameters = [], $locale = null, $domain = 'messages'): string
    {
        return $this->slim->sfTranslator->trans($id, $parameters, $domain, $locale);
    }

    /**
     * Return site language
     * @return string
     */
    public function getSiteLang(): string
    {
        $siteLang = $this->slim->session->get('lang', 'en');
        return $siteLang === 'us' ? 'en' : $siteLang;
    }

    /**
     * Cast object to string
     * @param $var
     * @return string
     */
    public function formatString($var): string
    {
        return (string)$var;
    }

    /**
     * Override twig asset function to return assets with site path
     * @param $pathFile
     * @return string
     */
    public function overrideAsset($pathFile): string
    {
        return Config::read('path') . '/' . $pathFile . '?v=' . Config::read('app_version');
    }

    /**
     * Override twig dump function to return dumped object with proper dump style
     * @param Twig_Environment $env
     * @param $context
     * @return null|string
     */
    public function overrideDump(Twig_Environment $env, $context): ?string
    {
        if ($env->isDebug()) {
            ob_start();

            $count = func_num_args();
            if (2 === $count) {
                $vars = array();
                foreach ($context as $key => $value) {
                    if (!$value instanceof Twig_Template) {
                        $vars[$key] = $value;
                    }
                }

                dump($vars);
            } else {
                for ($i = 2; $i < $count; ++$i) {
                    dump(func_get_arg($i));
                }
            }
            return ob_get_clean();
        }
    }

    /**
     * Replace a string in a given word with other text
     * @param $toReplace
     * @param $replaceWith
     * @param $words
     * @return mixed
     */
    public function dynamicReplace(string $toReplace, string $replaceWith, string $words): string
    {
        return str_replace($toReplace, $replaceWith, $words);
    }

    /**
     * Json decode from a string value
     * @param $string
     * @return mixed
     */
    public function jsonDecode(String $string)
    {
        return json_decode($string);
    }

    /**
     * Format date to be like 11 Mars 1992 with correct month name
     * @param string $date
     * @return \DateTime|string
     */
    public function langSiteDateFormat(string $date)
    {
        $date = new \DateTime($date);
        $months = json_decode(Config::read('months'), true);
        $months = $months[$this->getSiteLang()]['monthsShort'];
        return $date->format('d') . ' ' . $months[(int)$date->format('m') - 1] . ' ' . $date->format('Y');
    }

    public function fixCoverLinkFr($link)
    {
        if ($this->getSiteLang() === 'fr') {
            switch (trim($link)) {
                case trim($this->trans('home.books.medicine_book', [], 'fr')):
                    $link = 'Etudes de médecine';
                    break;
                case trim($this->trans('home.books.nursing_book', [], 'fr')):
                    $link = 'Etudes paramédicales';
                    break;
                case trim($this->trans('home.books.health_book', [], 'fr')):
                    $link = 'Aide-soignant(e)';
                    break;
                case trim($this->trans('home.books.dentistry_book', [], 'fr')):
                    $link = 'Kinésithérapie';
                    break;
                case trim($this->trans('home.books.veterinary_book', [], 'fr')):
                    $link = 'Auxiliaire de puériculture';
                    break;
            }
        }
        return $link;
    }

    /**
     * The "sortByField" filter sorts an array of entries (objects or arrays) by the specified field's value
     *
     * Usage: {% for entry in master.entries|sortbyfield('ordering', 'desc') %}
     */
    public function sortByFieldFilter($content, $sort_by = null, $direction = 'asc') {

        if (is_a($content, 'Doctrine\Common\Collections\Collection')) {
            $content = $content->toArray();
        }

        if (!is_array($content)) {
            throw new \InvalidArgumentException('Variable passed to the sortByField filter is not an array');
        } elseif (count($content) < 1) {
            return $content;
        } elseif ($sort_by === null) {
            throw new Exception('No sort by parameter passed to the sortByField filter');
        } elseif (!self::isSortable(current($content), $sort_by)) {
            throw new Exception('Entries passed to the sortByField filter do not have the field "' . $sort_by . '"');
        } else {
            // Unfortunately have to suppress warnings here due to __get function
            // causing usort to think that the array has been modified:
            // usort(): Array was modified by the user comparison function
            @usort($content, function ($a, $b) use ($sort_by, $direction) {
                $flip = ($direction === 'desc') ? -1 : 1;

                if (is_array($a))
                    $a_sort_value = $a[$sort_by];
                else if (method_exists($a, 'get' . ucfirst($sort_by)))
                    $a_sort_value = $a->{'get' . ucfirst($sort_by)}();
                else
                    $a_sort_value = $a->$sort_by;

                if (is_array($b))
                    $b_sort_value = $b[$sort_by];
                else if (method_exists($b, 'get' . ucfirst($sort_by)))
                    $b_sort_value = $b->{'get' . ucfirst($sort_by)}();
                else
                    $b_sort_value = $b->$sort_by;

                if ($a_sort_value == $b_sort_value) {
                    return 0;
                } else if ($a_sort_value > $b_sort_value) {
                    return (1 * $flip);
                } else {
                    return (-1 * $flip);
                }
            });
        }
        return $content;
    }


    /**
     * Validate the passed $item to check if it can be sorted
     * @param $item mixed Collection item to be sorted
     * @param $field string
     * @return bool If collection item can be sorted
     */
    private static function isSortable($item, $field) {
        if (is_array($item))
            return array_key_exists($field, $item);
        elseif (is_object($item))
            return isset($item->$field) || property_exists($item, $field);
        else
            return false;
    }

    public function getEnviroment()
    {
        return Config::read('environment');;
    }

    /**
     * check if the book with the given isbn have student Ancillary
     * @param $isbn
     * @return mixed
     * @throws \Exception
     */
    public function checkStudentResources($isbn) {
        $container = $this->slim->getContainer();
        /** @var SlxWebService $slxWebService */
        $bookService = $container->get(SLX::class)->getBookService();
        return $bookService->hasStudentResources($isbn);
    }

    /**
     * check if the book with the given isbn have Instructor Ancillary
     * @param $isbn
     * @return mixed
     * @throws \Exception
     */
    public function checkInstructorResources($isbn) {
        $container = $this->slim->getContainer();
        /** @var SlxWebService $slxWebService */
        $bookService = $container->get(SLX::class)->getBookService();
        return $bookService->hasInstructorResources($isbn);
    }

    public function isInteger($value)
    {
        return  is_integer($value);
    }

    /**
     *  get list of banners by region for display in the home page carousel
     * @param int $region
     * @return array[]
     */
    public static function getBannersByRegion(?int $region) {
        $bannersReg7 = [
            [
                'title'=> "Rang and Dale’s Pharmacology",
                'description'=> "Internationally acknowledged as the core textbook for students of pharmacology, providing accessible, up-to-date information on drugs and their mechanism of action.",
                'coverDesktop' => "assets/img/home-page/Banner1@1x.png",
                'coverMobile' => "assets/img/home-page/Banner-mobile1@1x.png",
                'link' => "/book/details/9780323873956"
            ], [
                'title'=> "Nursing titles",
                'description'=> "<span>Discover the right resource for your course</span> Explore our nursing textbooks.",
                'coverDesktop' => "assets/img/home-page/Banner2@1x.png",
                'coverMobile' => "assets/img/home-page/Banner-mobile2@1x.png",
                'link' => "/books/hs?sub=Adult+General+Nursing%23Nursing"
            ], [
                'title'=> "Macleod's Clinical Examination",
                'description'=> "The textbook of choice for students who understand that excellence in clinical examination is integral to good practice.",
                'coverDesktop' => "assets/img/home-page/Banner3@1x.png",
                'coverMobile' => "assets/img/home-page/Banner-mobile3@1x.png",
                'link' => "/book/details/9780323847704"
            ],[
                'title'=> "Explore our essential anatomy books",
                'description'=> "Focusing on the core information medical students need to know with world-renowned illustrations to enhance understanding and retention.",
                'coverDesktop' => "assets/img/home-page/Banner4@1x.png",
                'coverMobile' => "assets/img/home-page/Banner-mobile4@1x.png",
                'link' => "/books/hs?sub=Anatomy%23Medicine"
            ]
        ];
        $bannersReg12 = [[
            'title'=> "Top-Titel für die Pflegeausbildung",
            'description'=> "Jetzt kostenlos digitale Prüfexemplare anfordern",
            'coverDesktop' => "assets/img/home-page/Banner-de3@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-de3@1x.png",
            'link' => "/books/all?cat=Pflege"
        ],[
            'title'=> "Top-Titel für das Medizinstudium",
            'description'=> "Jetzt kostenlos digitale Prüfexemplare anfordern",
            'coverDesktop' => "assets/img/home-page/Banner-de6@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-de6@1x.png",
            'link' => "/books/all?cat=Medizinstudium"
        ],[
            'title'=> "Top-Titel für angehende Heilpraktiker*innen",
            'description'=> "Jetzt kostenlos digitale Prüfexemplare anfordern",
            'coverDesktop' => "assets/img/home-page/Banner-de1@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-de1@1x.png",
            'link' => "/books/all?cat=Heilpraktiker%2FHP+Psychotherapie"
        ],[
            'title'=> "Top-Titel für angehende Sprachtherapeut*innen",
            'description'=> "Jetzt kostenlos digitale Prüfexemplare anfordern",
            'coverDesktop' => "assets/img/home-page/Banner-de2@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-de2@1x.png",
            'link' => "/books/all?cat=Logop%C3%A4die"
        ],[
            'title'=> "Top-Titel für die Physiotherapeut*innen-Ausbildung",
            'description'=> "Jetzt kostenlos digitale Prüfexemplare anfordern",
            'coverDesktop' => "assets/img/home-page/Banner-de4@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-de4@1x.png",
            'link' => "/books/all?cat=Physiotherapie"
        ],[
            'title'=> "Top-Titel für den Rettungsdienst",
            'description'=> "Jetzt kostenlos digitale Prüfexemplare anfordern",
            'coverDesktop' => "assets/img/home-page/Banner-de5@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-de5@1x.png",
            'link' => "/books/all?cat=Rettungsdienst"
        ]];
        $bannersReg11 = [[
            'title'=> "Atlas Netter d’anatomie humaine",
            'description'=> "Le succès de cet ouvrage réside dans la qualité et la beauté du travail du Dr Frank H. Netter ainsi que du Dr Carlos A. G. Machado, parmi les plus grands illustrateurs médicaux au monde.",
            'coverDesktop' => "assets/img/home-page/Banner-fr1@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-fr1@1x.png",
            'link' => "/books/all?cat=Etudes+de+m%C3%A9decine"
        ],[
            'title'=> "Manuel de Diagnostics Infirmiers",
            'description'=> "Cette édition du Manuel de diagnostics infirmiers de Linda Carpenito a fait l’objet d’une mise à jour complète. L’ouvrage de référence couvre tous les diagnostics infirmiers approuvés par NANDA-I dans son manuel 2021-2023 et offre des conseils avisés sur les soins qui leur sont associés.",
            'coverDesktop' => "assets/img/home-page/Banner-fr2@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-fr2@1x.png",
            'link' => "/books/all?sub=Infirmier%28e%29%23Etudes+param%C3%A9dicales"
        ],[
            'title'=> "Méga-guide pratique de kinésithérapie",
            'description'=> "Conçu comme un ouvrage exhaustif mais à consultation résolument pratique, ce Méga Guide de Kinésithérapie réunit l’ensemble des connaissances que les kinésithérapeutes, en formation ou en exercice, doivent impérativement maîtriser.",
            'coverDesktop' => "assets/img/home-page/Banner-fr3@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-fr3@1x.png",
            'link' => "/books/all?cat=Kin%C3%A9sith%C3%A9rapie"
        ]];

        $bannersReg2 = [[
            'title'=> "Netter atlas de anatomía humana",
            'description'=> "Por primera vez, se presenta el atlas organizado por sistemas corporales. Tienen mismo contenido que el atlas tradicional con un abordaje regional.",
            'coverDesktop' => "assets/img/home-page/Banner-es1@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-es1@1x.png",
            'link' => "/book/details/9788413824185"
        ],[
            'title'=> "Guyton y Hall, fisiología médica",
            'description'=> "El principal tratado de fisiología médica del mundo. Asegura el máximo aprendizaje de conceptos complejos a estudiantes de grado y posgrado.",
            'coverDesktop' => "assets/img/home-page/Banner-es2@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-es2@1x.png",
            'link' => "/book/details/9788413820132"
        ],[
            'title'=> "Diagnósticos enfermeros, 2021-2023",
            'description'=> "La guía esencial de los diagnósticos enfermeros de NANDA-I. Incluye 46 diagnósticos nuevos y 67 revisados",
            'coverDesktop' => "assets/img/home-page/Banner-es3@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-es3@1x.png",
            'link' => "/book/details/9788413821276"
        ],[
            'title'=> "Guía Mosby de habilidades y procedimientos de enfermería",
            'description'=> "Guía de bolsillo que presenta 86 habilidades y procedimientos de enfermería en un formato simple y ordenados de la A a la Z.",
            'coverDesktop' => "assets/img/home-page/Banner-es4@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-es4@1x.png",
            'link' => "/book/details/9788491134152"
        ],[
            'title'=> "Microbiología médica",
            'description'=> "Cubre los principios básicos, el diagnóstico de laboratorio, la bacteriología, la virología, la micología y la parasitología para dominar la microbiología.",
            'coverDesktop' => "assets/img/home-page/Banner-es5@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-es5@1x.png",
            'link' => "/book/details/9788491138082"
        ],[
            'title'=> "Gray. Anatomía para estudiantes",
            'description'=> "Una obra clara y visual, con una presentación por regiones corporales y con un abordaje integrado de gran valor didáctico.",
            'coverDesktop' => "assets/img/home-page/Banner-es6@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-es6@1x.png",
            'link' => "/book/details/9788491136088"
        ]];

        $bannersReg4 = [[
            'title'=> "Gray's Anatomy for Students",
            'description'=> "VOLUME 1: Thorax, Upper limb, Lower limb, Abdomen, Pelvis and perineum <br> VOLUME 2: The body, Back, Head and neck, Neuroanatomy",
            'coverDesktop' => "assets/img/home-page/Banner-india1@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-india1@1x.png",
             'link' => "/book/details/9788131267486"
        ],[
            'title'=> "Textbook of Anatomy",
            'description'=> "The book provides practical application of anatomical facts through Clinical Correlation boxes in chapters. It also includes: Clinical Case Studies, 100+ New Illustrations, Competency Codes",
            'coverDesktop' => "assets/img/home-page/Banner-india2@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-india2@1x.png",
             'link' => "/book/details/9788131264850"
        ],[
            'title'=> "Gray’s Clinical Photographic Dissector of the Human Body",
            'description'=> "One stop unique dissection guide - Perform dissections with confidence by comparing the 1,098 full-color photographs to the cadavers you study.",
            'coverDesktop' => "assets/img/home-page/Banner-india3@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-india3@1x.png",
             'link' => "/book/details/9788131256923"
        ],[
            'title'=> "Guyton & Hall Textbook of Medical Physiology",
            'description'=> "<span>Third South Asia Edition</span> Meet the needs of undergraduate medical students and faculty in South Asia by aligning the book to the teaching methods in the subcontinent.",
            'coverDesktop' => "assets/img/home-page/Banner-india4@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-india4@1x.png",
             'link' => "/book/details/9788131257739"
        ],[
            'title'=> "Textbook of Medical Biochemistry",
            'description'=> "Book focuses primarily on clarity of the fundamental concepts with a logical sequence of events that aids learning.",
            'coverDesktop' => "assets/img/home-page/Banner-india5@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-india5@1x.png",
             'link' => "/book/details/9788131262511"
        ]];

        $bannersReg1 = [[
            'title'=> "Elevate your nursing curriculum with Elsevier's top-selling titles for nursing students.",
            'description'=> "",
            'coverDesktop' => "assets/img/home-page/Banner-anz1@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-anz1@1x.png",
             'link' => "/books/hs?sub=Adult+General+Nursing%23Nursing"
        ],[
            'title'=> "Elevate your medicine curriculum with Elsevier's top-selling titles for medical students.",
            'description'=> "",
            'coverDesktop' => "assets/img/home-page/Banner-anz2@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-anz2@1x.png",
             'link' => "/books/all?sb=title&s=talley"
        ],[
            'title'=> "Navigating the Maze of Research, 6th Edition",
            'description'=> "Produced by a high-profile editorial team, this book demystifies the world of research for nursing students.",
            'coverDesktop' => "assets/img/home-page/Banner-anz3@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-anz3@1x.png",
             'link' => "/book/details/9780729544832"
        ],[
            'title'=> "Critical Care Nursing, 5th Edition",
            'description'=> "An internationally acclaimed text endorsed by the Australian College of Critical Care Nurses (ACCCN).",
            'coverDesktop' => "assets/img/home-page/Banner-anz4@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-anz4@1x.png",
             'link' => "/book/details/9780729542975"
        ],  [
            'title'=> "Rang and Dale’s Pharmacology",
            'description'=> "Internationally acknowledged as the core textbook for students of pharmacology, providing accessible, up-to-date information on drugs and their mechanism of action.",
            'coverDesktop' => "assets/img/home-page/Banner1@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile1@1x.png",
             'link' => "/book/details/9780323873956"
        ]];
        $bannersReg9 = [[
            'title'=> "Explore the latest computing titles from Morgan Kaufmann",
            'description'=> "From fundamentals to advanced applications",
            'coverDesktop' => "assets/img/home-page/Banner-na1@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-na1@1x.png",
             'link' => "/books/st?sub=Artificial+Intelligence%2C+Expert+Systems+And+Knowledge-Based+Systems%23Mathematics+%26+Computer+Sciences"
        ],[
            'title'=> "Explore our indispensable virology books",
            'description'=> "Provide your students with a solid foundation",
            'coverDesktop' => "assets/img/home-page/Banner-na2@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-na2@1x.png",
             'link' => "/books/st?sub=Virology%23Life+Sciences"
        ],[
            'title'=> "Explore the latest materials books from Mike Ashby",
            'description'=> "Make the right material choice",
            'coverDesktop' => "assets/img/home-page/Banner-na3@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-na3@1x.png",
             'link' => "/books/all?sb=author&s=ashby"
        ],[
            'title'=> "Explore our Computer Organization titles",
            'description'=> "Award winning textbooks used by more than 40,000 students per year",
            'coverDesktop' => "assets/img/home-page/Banner-na4@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-na4@1x.png",
             'link' => "/books/all?sb=title&s=computer+organization+and+design&sub=Electrical+Engineering%23Engineering+%26+Materials+Sciences"
        ],[
            'title'=> "Explore our latest MATLAB titles",
            'description'=> "From the fundamentals to advanced applications",
            'coverDesktop' => "assets/img/home-page/Banner-na5@1x.png",
            'coverMobile' => "assets/img/home-page/Banner-mobile-na5@1x.png",
             'link' => "/books/all?sb=title&s=matlab"
        ]];
        $bannersReg8 = [
            [
                'title'=> "Rang and Dale’s Pharmacology",
                'description'=> "Internationally acknowledged as the core textbook for students of pharmacology, providing accessible, up-to-date information on drugs and their mechanism of action.",
                'coverDesktop' => "assets/img/home-page/Banner1@1x.png",
                'coverMobile' => "assets/img/home-page/Banner-mobile1@1x.png",
                'link' => "/book/details/9780323873956"
            ], [
                'title'=> "Macleod's Clinical Examination",
                'description'=> "The textbook of choice for students who understand that excellence in clinical examination is integral to good practice.",
                'coverDesktop' => "assets/img/home-page/Banner3@1x.png",
                'coverMobile' => "assets/img/home-page/Banner-mobile3@1x.png",
                'link' => "/book/details/9780323847704"
            ],[
                'title'=> "Explore our essential anatomy books",
                'description'=> "Focusing on the core information medical students need to know with world-renowned illustrations to enhance understanding and retention.",
                'coverDesktop' => "assets/img/home-page/Banner4@1x.png",
                'coverMobile' => "assets/img/home-page/Banner-mobile4@1x.png",
                'link' => "/books/hs?sub=Anatomy%23Medicine"
            ]
        ];

        $bannersRegDefault = $bannersReg7;
        switch ($region) {
            case 7:
                return $bannersReg7;
            case 12:
                return $bannersReg12;
            case 11:
                return $bannersReg11;
            case 2:
            case 6:
                return $bannersReg2;
            case 4:
                return $bannersReg4;
            case 5:
            case 9:
                return $bannersReg9;
            case 8:
            case 10:
                return $bannersReg8;
            case 1:
                return $bannersReg1;
            default:
                return $bannersRegDefault;
        }
    }
}

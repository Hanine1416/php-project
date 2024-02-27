<?php
/**
 * This file is part of the IC.
 * Created by mobelite.
 * Date: 5/24/18
 * Time: 13:12
 * @author: Mobelite <www.mobelite.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MBComponents\Services;

use Doctrine\ORM\EntityManager;
use lib\Config;
use MainBundle\Entity\WSDebug;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SOAP
 * @package Services
 */
abstract class SOAP implements SOAPInterface
{
    /** @var string $cacheFolder */
    private $cacheFolder = CACHE_DIRECTORY . '/soap/';

    /** @var ContainerInterface $container */
    protected $container;

    /**
     * SOAP constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        if (!is_dir($this->cacheFolder)) {
            mkdir($this->cacheFolder, 0777, true);
        }
        $this->container = $container;
    }

    /**
     * @param $func
     * @param $params
     * @param bool $cache
     * @return mixed
     */
    public function getSoap($func, $params, $cache = false)
    {
        /// CREATE FILE CACHE NAME
        $name = hash('sha256',$this->sanitize($func . serialize($params)));
        if ($cache || Config::read('debug')) {
            $fileName = $this->cacheFolder . $name;
            if (!file_exists($fileName) || (filemtime($fileName) < strtotime('-7 days'))) {
                return $this->soapRequest($func, $params, $cache, $name);
            } else {
               return json_decode(file_get_contents($fileName));
            }
        } else {
            return $this->soapRequest($func, $params, $cache, $name);
        }
    }

    /**
     * @param $string
     * @param bool $forceLowercase
     * @param bool $anal
     * @return mixed|string
     */
    private function sanitize($string, $forceLowercase = true, $anal = false)
    {
        $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
            "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
            "â€”", "â€“", ",", "<", ".", ">", "/", "?");
        $clean = trim(str_replace($strip, "", strip_tags($string)));
        $clean = preg_replace('/\s+/', "-", $clean);
        $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean;
        if ($forceLowercase) {
            if (function_exists('mb_strtolower')) {
                $clean = mb_strtolower($clean, 'UTF-8');
            } else {
                $clean = strtolower($clean);
            }
        }

        return $clean;
    }

    /**
     * @param string $func
     * @param $params
     * @param $cache
     * @param $name
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function soapRequest(string $func, $params, $cache, $name)
    {
        $opts = array(
            'ssl' => array('ciphers' => 'RC4-SHA')
        );

        if ($func == 'elsGet_PickList' || $func == 'elsGet_CatalogEx') {
            $params = $this->addLanguageAndRegionParams($params);
        }

        /** create the client object */
        $soapClient = new \SoapClient(
            Config::read('pathWebservice'),
            array('encoding' => 'UTF-8',
                'stream_context' => stream_context_create($opts),
                'cache_wsdl' => WSDL_CACHE_NONE,
                'connection_timeout' => 160
            )
        );

        //Use the functions of the client, the params of the function are in
        //the associative array
        $response = $soapClient->{$func}($params);

        /// CHECK FILE CACHE AGE
        if ($cache) {
            $filename = (string)$this->cacheFolder . $name;
            touch($filename);
            $fl = fopen($filename, 'w');
            fwrite($fl, json_encode($response));
        }

        /** save ws execution query & result to file */
        if (Config::read('debug')) {

            /** @var EntityManager $entityManager */
            $entityManager = $this->container->get(EntityManager::class);

            $soapDebug = $entityManager->getRepository(WSDebug::class)->find($func);
            if (!$soapDebug) {
                $soapDebug = new WSDebug($func);
                $soapDebug->setQuery($params);
                $responseArray = json_encode($response, true);
                $soapDebug->setResponse($responseArray);
                $entityManager->persist($soapDebug);
                $entityManager->flush();
            }
        }

        return $response;
    }

    /**
     * @param $params
     * @return mixed
     */
    private function addLanguageAndRegionParams(array $params):array
    {
        if (!isset($params['language']) || !isset($params['region'])) {
            $varSesLang = $this->container->getParameter('lang');
            $language = Config::read('wsLanguages')[$varSesLang];
            $region = Config::read('crrregion');

            $params['language'] = $language;
            $params['region'] = $region;
        }

        return $params;
    }
}

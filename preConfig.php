<?php

use lib\Config;

Config::write('lang', 'en');
Config::write('languages', ['en', 'es', 'de','in','fr','us','anz','sea','row','ind']);
Config::write('wsLanguages', array('en' => 'English', 'fr' => 'French', 'es' => 'Spanish', 'de' => 'German', 'in' => 'India','us' => 'United States','anz' =>'ANZ','sea'=>'SEA','row'=>'ROW','ind'=>'Indonesia'));
Config::write('langFiles', array('en'=>'en_GB','fr'=>'fr_FR','es'=>'es_SP','de'=>'de_GR','in'=>'en_IN','us'=>'en_US','anz' => 'en_ANZ','sea'=>'en_SEA','row'=>'en_row','ind'=>'en_ind'));
Config::write('langRegions', array('en' => 7, 'fr' => 11, 'es' => 6, 'de' => 12,'in' => 4,'us' => 9,'anz'=> 1,'sea'=> 8, 'row' => 5, 'ind' => 10));

Config::write('templateLocal', '../src/Resources/views/'); // override old template directory

/** Add to domain path */
$configDomainPaths = Config::has('domainPaths') ? Config::read('domainPaths') : [];

$configDomainPaths['4/in'] = Config::read('path') . '/4/in';
$configDomainPaths['4/es'] = Config::read('path') . '/4/es';
$configDomainPaths['4/en'] = Config::read('path') . '/4/en';

$configDomainPaths['10/in'] = Config::read('path') . '/10/in';
$configDomainPaths['7/in'] = Config::read('path') . '/7/in';
$configDomainPaths['6/in'] = Config::read('path') . '/6/in';

$configDomainPaths['11/fr'] = Config::read('path') . '/11/fr';
$configDomainPaths['12/de'] = Config::read('path') . '/12/de';

/** login timeout */
Config::write('sessionTimeout', 3600);

Config::write('projectEmail', '');
/** token validation hour */
Config::write('tokenValidationHour', 6);

/** app version */
Config::write('app_version', '3.0.1');

Config::write('months', file_get_contents(__DIR__ . '/months.i18n.json'));
/**
* @var string
*/
const CACHE_DIRECTORY = __DIR__ . '/cache/';
/**
* @var string
*/
const LOG_DIRECTORY = __DIR__ . '/cache/logs/';
/**
* @var string
*/
const SESSION_DIRECTORY = __DIR__ . '/cache/sessions';
/**
* @var string
*/
const TEMPLATE_DIRECTORY = __DIR__ . '/src/Resources/views/';
/**
* @var string
*/
const TRANSLATIONS_DIRECTORY = __DIR__ . '/src/Resources/translations/';

/** Define debug param to false if not defined in config.php file */
if (!Config::has('debug')) {
Config::write('debug', true);
}

Config::write('langRegions',[
'en' => [0,6,7],
'es' => [2,6],
'fr' => [11],
'de' => [12],
'in' => [4],
'us' => [9],
'anz' => [1],
'sea'=> [8],
'row' => [5],
'ind'=> [10],
]);
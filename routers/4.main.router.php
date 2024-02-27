<?php
/**
 * Created by PhpStorm.
 * User: Mobelite
 * Date: 28/05/2018
 * Time: 12:33
 * @author: Mobelite <www.mobelite.fr>
 */

/** Home page router */
$app->get('/', static function (){
    $mainController = new \MainBundle\Controller\MainController();
    $mainController->home();
})->setName('main');

/** Contact us page router */
$app->map('/contact-us', static function (){
    $mainController = new \MainBundle\Controller\MainController();
    $mainController->contactUs();
})->via('POST', 'GET')->setName('contactUs');

/** Contact us page router */
$app->map('/contact-us/:isbn', static function ($isbn){
    $mainController = new \MainBundle\Controller\MainController();
    $mainController->contactUs($isbn);
})->via('POST', 'GET')->setName('contactUsIsbn');

/** Get institutions by ajax call router */
$app->post('/institutions', static function () {
    $mainController = new \MainBundle\Controller\MainController();
    $mainController->getInstitutions();
})->setName('institutions');

/** Get departments by ajax call router */
$app->post('/departments', static function () {
    $mainController = new \MainBundle\Controller\MainController();
    $mainController->getDepartments();
})->setName('departments');

/** Get current language available countries by ajax call router */
$app->post('/countries', static function () {
    $mainController = new \MainBundle\Controller\MainController();
    $mainController->getCountries();
})->setName('countries');

/** Get country 's cities by ajax call router */
$app->get('/cities/:country', static function ($country) {
    $mainController = new \MainBundle\Controller\MainController();
    $mainController->getCities($country);
})->setName('cities');

/** Get country 's states by ajax call router */
$app->get('/states/:country', static function ($country) {
    $mainController = new \MainBundle\Controller\MainController();
    $mainController->getStates($country);
})->setName('states');

/** Get profession's specialities by ajax call router */
$app->post('/specialities', static function () {
    $mainController = new \MainBundle\Controller\MainController();
    $mainController->getSpecialities();
})->setName('specialities');

/** Get department address by ajax call router */
$app->post('/addresses', static function () {
    $mainController = new \MainBundle\Controller\MainController();
    $mainController->getAddresses();
})->setName('addresses');

/** Cookie page router */
$app->get('/cookies', static function (){
    $mainController = new \MainBundle\Controller\MainController();
    $mainController->cookiePage();
})->setName('cookies');

/** Check CEP valid for br address */
$app->get('/cep/:cep', static function ($cep){
    $mainController = new \MainBundle\Controller\MainController();
    $mainController->getAddressFromCep($cep);
})->setName('address-cep');

/** Add book to reading list */
$app->post('/add-book', static function (){
    $mainController = new \MainBundle\Controller\MainController();
    $mainController->addBookReadingList();
})->setName('add-book');

/**
 * Save the feedback given by user
 */
$app->post('/:reg/:lang/give-feedback', function () {
    $mainController = new \MainBundle\Controller\MainController();
    $mainController->giveFeedback();
})->setName('give-feedback');

/** show features */
$app->get('/features',static function () {
    $controller = new \MainBundle\Controller\MainController();
    $controller->showFeatures();
})->setName('features');

/** language and catalog popup */
$app->post('/language-catalog-popup',static function () {
    $controller = new \MainBundle\Controller\MainController();
    $controller->languageCatalog();
})->setName('languageCatalog');

/** Ajax method to display books on search bar */
$app->post('/search-bar-show',static function () {
    $controller = new \MainBundle\Controller\MainController();
    $controller->showSearchBar();
})->setName('result-searchbar');


/**
 * Redirect to book details page with the given isbn
 * Throw NotFoundException if the route is not defined
 */
$app->get('/(:params+)', static function ($params) use ($app){

    /** redirect url with numeric value to book details page */
    if (count($params) === 1 && is_numeric($params[0])) {
        $app->response->redirect($app->urlFor('showBookDetails', array('isbn' => $params[0])), 303);
    } elseif(count($params)>0 && in_array($params[0], ['7', '11', '6', '12', '10', '4'], true) && in_array($params[1],['en','fr','es','de','br','in']) || $params[0] == 'main')  {
        $app->response->redirect($app->urlFor('main'), 303);
    } else {
        throw new \MBComponents\Exceptions\NotFoundException();
    }
});

$app->post('/load-more', static function (){
    $mainController = new \MainBundle\Controller\MainController();
    $mainController->loadMoreRecommended();
})->setName('load-more');

$app->post('/load-more-shared-book', static function (){
    $mainController = new \MainBundle\Controller\MainController();
    $mainController->loadMoreSharedBook();
})->setName('load-more-shared-book');


<?php
/**
 * Created by PhpStorm.
 * User: Mobelite
 * Date: 28/05/2018
 * Time: 12:34
 * @author: Mobelite <www.mobelite.fr>
 */

use Slim\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * protect route that should be accessed with authentication & role admin
 * @param Route $route
 * @return null|string
 */
$adminGard = function (Route $route) use ($app) {
    $adminSession = $app->session->get('adminUser');
    /** check if the admin is logged in or not */
    if (!(isset($adminSession) && !empty($adminSession))) {
        /** if the request is ajax then return a json response with not authorized code 400 */
        if ($app->sfRequest->isXmlHttpRequest()) {
            $response = new JsonResponse(['success' => false], 400);
            return $response->getContent();
        /** redirect the admin login page */
        } else {
            $app->redirectTo('admin');
        }
    }
    return null;
};

/** admin login/dashboard page route */
$app->get('/admin', static function () {
    $adminController = new \UserBundle\Controller\AdminController();
    if ($adminController->isLoggedIn()) {
        $adminController->dashboard();
    } else {
        $adminController->login();
    }
})->setName('admin');

/** admin logout  router */
$app->get('/admin/logout', static function () {
    $adminController = new \UserBundle\Controller\AdminController();
    $adminController->logout();
})->setName('admin-logout');

/** admin edit cover page route */
$app->post('/admin/cover/edit', $adminGard, static function () {
    $adminController = new \UserBundle\Controller\AdminController();
    $adminController->editCover();
})->setName('admin-edit-cover');

/** admin faq page */
$app->get('/admin/faq', $adminGard, static function () {
    $adminController = new \UserBundle\Controller\AdminController();
    $adminController->faqPage();
})->setName('admin-faq');

/** admin add faq into the faq page */
$app->post('/admin/faq/add', $adminGard, static function () {
    $adminController = new \UserBundle\Controller\AdminController();
    $adminController->addFaq();
})->setName('admin-add-faq');

/** admin edit faq into the faq page*/
$app->post('/admin/faq/:token/edit', $adminGard, static function ($token) {
    $adminController = new \UserBundle\Controller\AdminController();
    $adminController->editFaq($token);
})->setName('admin-edit-faq');

/** admin edit faq order into the faq page*/
$app->post('/admin/faq/edit', $adminGard, static function () {
    $adminController = new \UserBundle\Controller\AdminController();
    $adminController->editOrderFaq();
})->setName('admin-edit-order-faq');

/** admin delete faq */
$app->post('/admin/faq/:token/delete', $adminGard, static function ($token) {
    $adminController = new \UserBundle\Controller\AdminController();
    $adminController->deleteFaq($token);
})->setName('admin-delete-faq');

/** admin What's New page */
$app->map('/admin/what-new', $adminGard, static function () {
    $adminController = new \UserBundle\Controller\AdminController();
    $adminController->whatNew();
})->via('GET', 'POST')->setName('admin-what-new');

/** admin add cookie into the cookie table by ajax post request router */
$app->post('/admin/cookie-table/add', $adminGard, static function () {
    $adminController = new \UserBundle\Controller\AdminController();
    $adminController->addCookie();
})->setName('admin-add-cookie-table');

/** admin show/hide cookie in the cookie table by ajax post request router */
$app->post('/admin/cookie-table/:token/edit', $adminGard, static function ($token) {
    $adminController = new \UserBundle\Controller\AdminController();
    $adminController->editCookie($token);
})->setName('admin-edit-cookie-table');

/** admin show/hide cookie in the cookie table by ajax post request router */
$app->post('/admin/cookie-table/:token/enable', $adminGard, static function ($token) {
    $adminController = new \UserBundle\Controller\AdminController();
    $adminController->enableCookie($token);
})->setName('admin-enable-cookie-table');

/** admin delete cookie from the cookie table by ajax post request router */
$app->post('/admin/cookie-table/:token/delete', $adminGard, static function ($token) {
    $adminController = new \UserBundle\Controller\AdminController();
    $adminController->deleteCookie($token);
})->setName('admin-delete-cookie-table');

/** admin faq page */
$app->get('/admin/banner', $adminGard,static function () {
    $adminController = new \UserBundle\Controller\AdminController();
    $adminController->bannerPage();
})->setName('admin-banner');

/** admin add faq into the faq page */
$app->post('/admin/banner/add', $adminGard,static function () {
    $adminController = new \UserBundle\Controller\AdminController();
    $adminController->addBanner();
})->setName('admin-add-banner');

/** admin edit banner*/
$app->post('/admin/banner/:token/edit', $adminGard,static function ($token) {
    $adminController = new \UserBundle\Controller\AdminController();
    $adminController->editBanner($token);
})->setName('admin-edit-banner');

/** admin disable banner */
$app->post('/admin/banner/:token/disable', $adminGard,static function ($token) {
    $adminController = new \UserBundle\Controller\AdminController();
    $adminController->disableBanner($token,false);
})->setName('admin-disable-banner');

/** admin enable banner */
$app->post('/admin/banner/:token/enable', $adminGard,static function ($token) {
    $adminController = new \UserBundle\Controller\AdminController();
    $adminController->disableBanner($token, true);
})->setName('admin-enable-banner');

/** admin delete banner */
$app->post('/admin/banner/:token/delete', $adminGard,static function ($token) {
    $adminController = new \UserBundle\Controller\AdminController();
    $adminController->deleteBanner($token);
})->setName('admin-delete-banner');

/** admin login ajax request router */
$app->post('/admin/login', static function () {
    $adminController = new \UserBundle\Controller\AdminController();
    $adminController->login();
})->setName('admin-login');

/** admin get active banner */
$app->get('/admin/getActiveBanner', $adminGard,static function () {
    $adminController = new \UserBundle\Controller\AdminController();
    $adminController->getActiveBanner();
})->setName('active-banner');

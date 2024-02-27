<?php
/**
 * Created by PhpStorm.
 * User: Mobelite
 * Date: 28/05/2018
 * Time: 12:33
 * @author: Mobelite <www.mobelite.fr>
 */

/** request reset password route */
$app->post('/reset-password', static function () {
    $controller = new \UserBundle\Controller\ResettingController();
    $controller->index();
})->setName('reset-password');

/** reset password route */
$app->map('/reset/:token', static function ($token) use ($app) {
    $controller = new \UserBundle\Controller\ResettingController();
    if ($controller->isLoggedIn()) {
        $app->redirectTo('main');
    } else {
        $controller->reset($token);
    }
})->via('POST', 'GET')->setName('reset');

/** request registration  route*/
$app->map('/register', static function () use ($app) {
    $controller = new \UserBundle\Controller\RegisterController();
    if ($controller->isLoggedIn()) {
        $app->redirectTo('main');
    } else {
        $controller->register();
    }
})->via('GET', 'POST')->setName('register');

/** request registration  route*/
$app->post('/email-validator',static function () {
    $controller = new \UserBundle\Controller\RegisterController();
    $controller->emailValidation();
})->setName('email-validator');

/** request registration  route*/
$app->map('/complete-register/:token', static function ($token) use ($app) {
    $controller = new \UserBundle\Controller\RegisterController();
    if ($controller->isLoggedIn()) {
        $app->redirectTo('main');
    } else {
        $controller->completeRegistration($token);
    }
})->via('GET', 'POST')->setName('complete-register');

/** thank you page  route*/
$app->get('/registration-completed', static function () {
    $controller = new \UserBundle\Controller\RegisterController();
    $controller->registrationCompleted();
})->setName('registration-completed');

/** confirm authentication route */
$app->map('/confirm-authentication', static function () {
    $controller = new \UserBundle\Controller\SecurityUserController();
    $controller->confirmAuthentication();
})->via('GET', 'POST')->setName('confirm-authentication');

/** resend code route */
$app->map('/resend-code', static function () {
    $controller = new \UserBundle\Controller\SecurityUserController();
    $controller->resendCode();
})->via('GET', 'POST')->setName('resend-code');

/** logout action */
$app->get('/faq', static function () {
    $controller = new \UserBundle\Controller\ProfileController();
    $controller->faq();
})->setName('faq');

/** add user institution route */
$app->post('/user/institution', static function () {
    $controller = new \UserBundle\Controller\ProfileController();
    $controller->addUserInstitution();
})->setName('add-institution');

/** update user institution route */
$app->put('/user/institution', static function () {
    $controller = new \UserBundle\Controller\ProfileController();
    $controller->updateUserInstitution();
})->setName('update-institution');

/** delete user institution route */
$app->delete('/user/institution', static function () {
    $controller = new \UserBundle\Controller\ProfileController();
    $controller->deleteUserInstitution();
})->setName('delete-institution');

/** delete user institution route */
$app->put('/user/institution/disable', static function () {
    $controller = new \UserBundle\Controller\ProfileController();
    $controller->disableUserInstitution();
})->setName('disable-institution');

/** user inspection copies page */
/*$app->map('/profile/inspection-copies', static function () use ($app) {
    $controller = new \UserBundle\Controller\ProfileController();
    (!$controller->isLoggedIn()) ? $app->redirectTo('main') :  $controller->showBooks();
})->via('GET')->setName('my-inspection-copies');*/

/** export books file*/
$app->map('/profile/export-file', static function () {
    (new \UserBundle\Controller\ProfileController())->exportWord();
})->via('GET','POST')->setName('export-file');

/** export reading list file*/
$app->map('/profile/export-list', static function () {
    (new \UserBundle\Controller\ProfileController())->exportReadingListWord();
})->via('GET','POST')->setName('export-list');

/** export book details in product page*/
$app->map('/profile/export-book/:isbn', static function ($isbn) {
    $controller = new \UserBundle\Controller\ProfileController($isbn);
    $controller->exportBookWord($isbn);
})->via('GET','POST')->setName('export-book');

/** user show or update personal details page */
$app->map('/profile/personal-details', static function () use ($app) {
    $controller = new \UserBundle\Controller\ProfileController();
    (!$controller->isLoggedIn()) ? $app->redirectTo('main') :  $controller->personalDetails();
})->via('GET', 'PUT')->setName('my-personal-details');
/** user change password page */
$app->map('/profile/change-password', static function () use ($app) {
    $controller = new \UserBundle\Controller\ProfileController();
    (!$controller->isLoggedIn()) ? $app->redirectTo('main') :  $controller->changePassword();
})->via('GET', 'POST')->setName('change-password');



/** add user delivery address page */
$app->post('/profile/delivery-address', static function () use ($app) {
    $controller = new \UserBundle\Controller\ProfileController();
    (!$controller->isLoggedIn()) ? $app->redirectTo('main') :  $controller->addUserAddress();
})->setName('new-delivery-address');

/** update user delivery address page */
$app->put('/profile/delivery-address', static function () {
    $controller = new \UserBundle\Controller\ProfileController();
    $controller->updateUserAddress();
})->setName('update-delivery-address');

/** get user delivery address page */
$app->get('/profile/delivery-address/:addressId', static function ($addressId) {
    $controller = new \UserBundle\Controller\ProfileController();
    $controller->getAddressDetails($addressId);
})->setName('get-delivery-address');


/** disable user institution route */
$app->put('/profile/disable-address', static function () {
    $controller = new \UserBundle\Controller\ProfileController();
    $controller->disableUserAddress();
})->setName('disable-address');

/** delete user delivery address route */
$app->delete('/profile/delete-address', static function () {
    $controller = new \UserBundle\Controller\ProfileController();
    $controller->deleteUserAddress();
})->setName('delete-address');

/** user institutions */
$app->map('/profile/my-institutions', static function () use ($app) {
    $controller = new \UserBundle\Controller\ProfileController();
    (!$controller->isLoggedIn()) ? $app->redirectTo('main') :  $controller->showInstitutions();
}) ->via('GET', 'POST')->setName('my-institutions');

/** user education consultant page */
$app->map('/profile/education-consultant', static function () use($app) {
    $controller = new \UserBundle\Controller\ProfileController();
    (!$controller->isLoggedIn()) ? $app->redirectTo('main') :  $controller->showEducationConsultant();
})->via('GET')->setName('my-education-consultant');

/** user education consultant page */
$app->map('/upload-file',static function () {
    (new \UserBundle\Controller\ProfileController())->uploadUserFile();
})->via('POST')->setName('upload-user-file');

/** update user guide action */
$app->post('/update-guide', function () {
    (new \UserBundle\Controller\ProfileController())->updateGuide();
})->setName('update-guide');

/** update profileFileProvided  action */
$app->post('/update-profile-file-provided', function () {
    (new \UserBundle\Controller\ProfileController())->updateProfileFileProvided();
})->setName('update-profile-file-provided');

/** login action */
$app->post('/login', static function () {
    (new \UserBundle\Controller\SecurityUserController())->login();
})->setName('login');

/** logout action */
$app->get('/logout', static function () {
    (new \UserBundle\Controller\SecurityUserController())->logout();
})->setName('logout');

$app->get('/profile/update-version',static function () {
    $controller = new \UserBundle\Controller\ProfileController();
    $controller->updateUserVersion();
})->setName('update-version');


$app->get('/profile/update-virtual-tour',static function () {
    $controller = new \UserBundle\Controller\ProfileController();
    $controller->updateUserVersion();
})->setName('update-virtual-tour');


/** show historical data page */
$app->map('/profile/historical-data',static function () use ($app) {
    $controller = new \UserBundle\Controller\ProfileController();
    (!$controller->isLoggedIn()) ? $app->redirectTo('main') :  $controller->showHistoricalData();
})->via('GET')->setName('historical-data');


/** show reading list page */
$app->map('/my-books',static function () use ($app) {
    $controller = new \UserBundle\Controller\ProfileController();
    (!$controller->isLoggedIn()) ? $app->redirectTo('main') :  $controller->showReadingLists();
})->via('GET')->setName('my-books');

/** remove book from reading list*/
$app->map('/reading-list-remove-book',static function () {
    $controller = new \UserBundle\Controller\ProfileController();
    $controller->removeBook();
})->via('GET')->setName('reading-list-remove-book');

/** get reading lists which doesen't contain selected book */
$app->map('/reading-list-copy-move-modal',static function () {
    $controller = new \UserBundle\Controller\ProfileController();
    $controller->showCopyModalContent();
})->via('GET')->setName('reading-list-copy-move-book-modal');

/** Add book to reading list */
$app->post('/add-reading-list', static function (){
    $controller = new \UserBundle\Controller\ProfileController();
    $controller->addNewReadingList();
})->setName('add-reading-list');

/** get book content */
$app->map('/reading-list-book-content',static function () {
    $controller = new \UserBundle\Controller\ProfileController();
    $controller->getBookContent();
})->via('GET')->setName('reading-list-book-content');


/** get reading lists which doesen't contain selected book */
$app->map('/reading-list-book-category-update',static function () {
    $controller = new \UserBundle\Controller\ProfileController();
    $controller->updateBookCategory();
})->via('PUT')->setName('reading-list-book-category-update');

/** set  */
$app->map('/list-name-update',static function () {
    $controller = new \UserBundle\Controller\ProfileController();
    $controller->updateListName();
})->via('PUT')->setName('list-name-update');

/** update user categories **/
$app->post('/profile/update-user-interests',static function () {
    $controller = new \UserBundle\Controller\ProfileController();
    $controller->updateUserInterests();
})->setName('update-user-interests');



/** identify verification page */
$app->map('/profile/identify-verification',static function () use ($app) {
    $controller = new \UserBundle\Controller\ProfileController();
   (!$controller->isLoggedIn()) ? $app->redirectTo('main'): $controller->identifyVerification();
})->via('GET')->setName('identify-verification');

/** verify account */
$app->post('/verify-account',static function () use ($app) {
    $controller = new \UserBundle\Controller\ProfileController();
    if (!$controller->isLoggedIn()) {
        $app->redirectTo('main');
    } else {
        $controller->verifyAccount();
    }
})->setName('verify-account');

/** remove reading list  */
$app->delete('/reading-list-remove-list',static function () {
    $controller = new \UserBundle\Controller\ProfileController();
    $controller->removeReadingList();
})->setName('reading-list-remove-list');

/** read notification */
$app->get('/profile/update-notification',static function () {
    $controller = new \UserBundle\Controller\ProfileController();
    $controller->updateNotification();
})->setName('update-notification');

/** get reading lists which doesen't contain selected book */
$app->map('/get-allowed-reading-list',static function () {
    $controller = new \UserBundle\Controller\ProfileController();
    $controller->getAllowedReadingList();
})->via('GET')->setName('get-allowed-reading-list');

/** recommendation profile page */
$app->get('/profile/recommendations', static function () {
    $controller = new \UserBundle\Controller\ProfileController();
    $controller->recommendations();
})->setName('recommendations-page');

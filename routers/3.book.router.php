<?php
/**
 * Created by PhpStorm.
 * User: Mobelite
 * Date: 28/05/2018
 * Time: 12:34
 * @author: Mobelite <www.mobelite.fr>
 */

/** show book details router */
$app->get('/book/details/:isbn', static function ($isbn) {
    $bookController = new \MainBundle\Controller\BookController();
    $bookController->showBookDetail($isbn);
})->setName('showBookDetails');

/** duplicated route for marketing tracking */
$app->get('/book/details/:isbn(:params+)', static function ($isbn) {
    $bookController = new \MainBundle\Controller\BookController();
    $bookController->showBookDetail($isbn);
});

/** request digital or print book router */
$app->post('/request-book', static function () {
    $bookController = new \MainBundle\Controller\BookController();
    $bookController->requestBook();
})->setName('request-book');

$app->get('/course-details/:productId', static function ($productId) {
    $bookController = new \MainBundle\Controller\BookController();
    $bookController->getCourseDetails($productId);
})->setName('course-details');

$app->put('/course-details/:productId', static function ($productId) {
    $bookController = new \MainBundle\Controller\BookController();
    $bookController->getCourseDetails($productId);
})->setName('edit-course-details');

$app->post('/review-book', static function () {
    $bookController = new \MainBundle\Controller\BookController();
    $bookController->reviewBook();
})->setName('review-book');

/** set book review and rating */
$app->post('/review-rating-book', static function () {
    $bookController = new \MainBundle\Controller\BookController();
    $bookController->reviewRatingBook();
})->setName('review-rating-book');

/** set book review and rating Ajax */
$app->get('/review-rating-book-ajax', static function () {
    $bookController = new \MainBundle\Controller\BookController();
    $bookController->reviewRatingBookAjax();
})->setName('review-rating-book-ajax');

/** Average review and rating Ajax */
$app->get('/average-review-book-ajax', static function () {
    $bookController = new \MainBundle\Controller\BookController();
    $bookController->averageReviewBookAjax();
})->setName('average-review-book-ajax');

/** set book review like */
$app->post('/like-review', static function () {
    $bookController = new \MainBundle\Controller\BookController();
    $bookController->likeReviewBook();
})->setName('like-review-book');

/** pre-order digital or print book router */
$app->map('/pre-order', static function () {
    $bookController = new \MainBundle\Controller\BookController();
    $bookController->requestBook();
})->via('POST', 'GET')->conditions(array('type' => '(Print|Digital)'))->setName('preOrderBook');

/** cancel book request route */
$app->put('/cancel-request', static function () {
    $controller = new \MainBundle\Controller\BookController();
    $controller->cancelBookRequest();
})->setName('cancelBookRequest');

/** Search books || display book subcategories || show all book router */
$app->map('/books/:catalogue',static function ($catalogue) {
        $bookController = new \MainBundle\Controller\BookController();
        $bookController->searchBooks($catalogue);
    }
)->via('GET','POST')->setName('search');

/** user read book route */
$app->get('/read/:isbn', static function ($isbn) {
    $bookController = new \MainBundle\Controller\BookController();
    $bookController->readBook($isbn);
})->setName('read-book');

/** load book reader plugin js route */
$app->get('/jsScript/(:timestamp)', static function () use ($app) {
    $bookController = new \MainBundle\Controller\BookController();
    $app->response->headers->set('Content-Type', 'application/javascript');
    $bookController->bookJsReader();
})->setName('book-js-reader');

/** share book with other user */
$app->post('/share-book', static function () {
    $bookController = new \MainBundle\Controller\BookController();
    $bookController->shareBook();
})->setName('share-book');

/** delete recommended book */
$app->post('/delete-recommendation', static function () {
    $bookController = new \MainBundle\Controller\BookController();
    $bookController->deleteRecommendation();
})->setName('delete-recommendation');

/** delete recommended book */
$app->post('/delete-shared-recommendation', static function () {
    $bookController = new \MainBundle\Controller\BookController();
    $bookController->deleteSharedRecommendation();
})->setName('delete-shared-recommendation');

/** student ancillary page */
$app->get('/student-resources/:isbn',static function ($isbn) use ($app){
    $controller = new \MainBundle\Controller\BookController();
    (!$controller->isLoggedIn()) ? $app->redirectTo('main'): $controller->showStudentTeacherResources($isbn,$student =true);
})->setName('student-resources');

/** teacher ancillary page */
$app->get('/teacher-resources/:isbn',static function ($isbn) use($app) {
    $controller = new \MainBundle\Controller\BookController();
    (!$controller->isLoggedIn()) ? $app->redirectTo('main'): $controller->showStudentTeacherResources($isbn);
})->setName('teacher-resources');

/** get list history */
$app->post('/get-list-history', static function () {
    $bookController = new \MainBundle\Controller\BookController();
    $bookController->getListHistory();
})->setName('get-list-history');

/**  */
$app->post('/update-filtered-book-session', static function () {
    $bookController = new \MainBundle\Controller\BookController();
    $bookController->updateFilteredBooks();
})->setName('update-filtered-book-session');

/** save the book when user click on read now button */
$app->post('/save-book', static function () use ($app) {
    $bookController = new \MainBundle\Controller\BookController();
    $bookController->saveUserEvent();
})->setName('save-book');

/** save list of books in session */
$app->post('/save_books-in-session',static function () {
    $controller = new \MainBundle\Controller\MainController();
    $controller->saveSessionBooks();
})->setName('save-books-in-session');

/** save list of books in session */
$app->get('/clear-books-session',static function () {
    $controller = new \MainBundle\Controller\MainController();
    $controller->clearBooksSession();
})->setName('clear-books-session');
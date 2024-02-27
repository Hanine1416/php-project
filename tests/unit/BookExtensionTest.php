<?php

use Doctrine\Common\Collections\ArrayCollection;
use MainBundle\Services\BookService;
use UserBundle\Services\UserService;
use MBComponents\Test\AppTester;
use MBComponents\Twig\Extensions\BookExtension;
use UserBundle\Entity\Notification;
use MainBundle\Entity\Cover;

class BookExtensionTest extends AppTester
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /** @var BookExtension */
    protected $bookExtension;

    public function __construct(?string $name = null, array $data = array(), string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->bookExtension = new BookExtension($this->app);
    }

    public function testRequestDetails()
    {
        /** @var BookService $bookService */
        $bookService = $this->app->getService(\MBComponents\Services\SLX::class)->getBookService();
        /** @var UserService $userService */
        $userService = $this->app->getService(\MBComponents\Services\SLX::class)->getUserService();
        //$this->assertNull($this->bookExtension->getRequestDetails('fake', $bookService));
//        $userService->authenticate(AppTester::UNIT_TEST_EMAIL);
//        $this->assertNull($this->bookExtension->getRequestDetails('fake', $bookService));
        /** Authenticate user before */
        $userService = $this->app->getService(\MBComponents\Services\SLX::class)->getUserService();
        $userService->authenticate(AppTester::UNIT_TEST_EMAIL);
        //$this->bookExtension->getRequestDetails('fake',$bookService);

    }

    public function testSortByDate()
    {
        $notification1 = new Notification();
        $notification1->setDate(new \DateTime('2019-06-25'));
        $notification2 = new Notification();
        $notification2->setDate(new \DateTime('2019-06-30'));
        $notification3 = new Notification();
        $notification3->setDate(new \DateTime('2019-06-20'));
        $notifications = $this->bookExtension->sortByDate(
            new ArrayCollection([$notification1, $notification3, $notification2])
        );
        $this->assertEquals($notification3, $notifications->last());
        $this->assertEquals($notification2, $notifications->first());
        $notification4 = new Notification();
        $notification5= new Notification();
        $notification4->setDate(new \DateTime('2019-06-20'));
        $notification5->setDate($notification4->getDate());
        $this->bookExtension->sortByDate(
            new ArrayCollection([$notification4, $notification5])
        );
    }

    public function testSortByReadAndDate()
    {
        $notification1 = new Notification();
        $notification1->setIsRead(false);
        $notification1->setDate(new \DateTime('2019-06-25'));

        $notification2 = new Notification();
        $notification2->setIsRead(true);
        $notification2->setDate(new \DateTime('2019-06-22'));

        $notification3 = new Notification();
        $notification3->setIsRead(false);
        $notification3->setDate(new \DateTime('2019-06-20'));

        $notifications = $this->bookExtension->sortByReadAndDate(
            new ArrayCollection([$notification2, $notification1, $notification3])
        );
        $this->assertEquals($notification1, $notifications->first());
        $this->assertEquals($notification2, $notifications->last());

        $notification2->setIsRead(false);
        $notification3->setIsRead(true);
        $this->assertNotEquals($notification2, $notification3);

        $notification2->setDate(new \DateTime('2020-06-20'));
        $notification3->setDate(new \DateTime('2020-06-22'));
        $this->assertNotEquals($notification2, $notification3);
        $this->bookExtension->sortByReadAndDate(
            new ArrayCollection([$notification2, $notification3])
        );
        $notification2->setDate(new \DateTime('2020-06-25'));
        $notification3->setDate(new \DateTime('2020-06-20'));
        $this->assertNotEquals($notification2, $notification3);
        $this->bookExtension->sortByReadAndDate(
            new ArrayCollection([$notification2, $notification3])
        );
        $notification5 = new Notification();
        $notification6 = new Notification();
        $notification5->setDate(new \DateTime('2020-06-25'));
        $notification6->setDate($notification5->getDate());
        $this->assertEquals($notification5, $notification6);
        $this->bookExtension->sortByReadAndDate(
            new ArrayCollection([$notification5, $notification6])
        );
    }

    public function testBookHasNotif()
    {
        $book1 = new stdClass();
        $book1->Isbn = '123456789';
        $book1->Format = 'Digital';
        $book2 = new stdClass();
        $book2->Isbn = '987654321';
        $book2->Format = 'Print';
        $notification1 = new Notification();
        $notification1->setIsbn($book1->Isbn);
        $notification1->setEventType('DigitalApproved');
        $notification2 = new Notification();
        $notification2->setIsbn($book2->Isbn);
        $notification2->setEventType('PrintApproved');
        $notifications = new ArrayCollection([$notification1,$notification2]);
        $this->assertTrue($this->bookExtension->isBookWithNotif($book1, 'Approved', $notifications));
        $this->assertFalse($this->bookExtension->isBookWithNotif($book1, 'Expired', $notifications));
        $this->assertTrue($this->bookExtension->isBookWithNotif($book2, 'Approved', $notifications));
        $this->assertFalse($this->bookExtension->isBookWithNotif(['Isbn'=>'159951357','Format'=>'Print'], 'Approved', $notifications));
    }

    public function testCreateMixedArray()
    {
        $mixedArray = $this->bookExtension->createMixedArray([], '123456789', ['adoptions']);
        $this->assertArrayHasKey('123456789', $mixedArray);
        $this->assertArrayHasKey('Adopted', $mixedArray['123456789']);
        $this->assertArrayHasKey('NotAdopted', $mixedArray['123456789']);
    }

    public function testSortByPreorderedLast()
    {
        $book1=['isbn'=>123,'PreOrder'=>true];
        $book5=['isbn'=>258,'PreOrder'=>false];
        $book3=['isbn'=>456,'PreOrder'=>true];
        $book4=['isbn'=>159,'PreOrder'=>false];
        $book2=['isbn'=>789,'PreOrder'=>false];
        $books = [$book1,$book2,$book3,$book5,$book4];
        $books = $this->bookExtension->sortByPreorderedLast($books);
        $this->assertEquals($books[0], $book2);
        $this->assertEquals($books[1], $book5);
        $this->assertEquals($books[2], $book4);
    }

    public function testIsBookWithNotification() {
        $book1=['Isbn'=>123,'Format'=>'Digital'];
        $status = 'Expired';
        $notification = new Notification();
        $notification->setIsbn('123');
        $notification->setIsRead(false);
        $notification->setEventType('FeedbackDue');
        $this->bookExtension->isBookWithNotif($book1,$status, new ArrayCollection([$notification]));

        $book2=['Isbn'=>451,'Format'=>'Digital'];
        $status = 'Adopted';
        $notification->setEventType('ConfirmationDue');
        $this->bookExtension->isBookWithNotif($book2,$status,new ArrayCollection([$notification]));

        $book3=['Isbn'=>447,'Format'=>'Print'];
        $status = 'Declined';
        $notification->setEventType('PrintStatusChanged');
        $this->bookExtension->isBookWithNotif($book3,$status,new ArrayCollection([$notification]));
    }

    public function testSubCategoryExist() {
        $categories = array('subcategories'=> array(array('text'=>'cat1'),array('text'=>'cat2')));
        $subCategories = ['cat1','cat2','cat3'];
        $this->bookExtension->subCategoryExist($categories ,$subCategories);
    }
    public function testGetSelectedCategoryType() {
        $categories  = array('PROMISH'=> array('category'=>'SH'),'PROMISI'=> array('category'=>'ST'));
        $selectedCategory = "SH";
        $this->bookExtension->getSelectedCategoryType($selectedCategory,$categories);
        $selectedCategory = "";
        $this->bookExtension->getSelectedCategoryType($selectedCategory,$categories);

    }
    public function testGetCoversByType() {
        $cover1 = new Cover('en');
        $cover2 = new Cover('en');
        $cover1->setCategory('hs');
        $cover2->setCategory('hs');
        $covers = array($cover1,$cover2);
        $this->bookExtension->getCoversByType($covers);
    }
    public function testIsBookHS() {
        $category = 'PROMISH';
        $this->bookExtension->isBookHS($category);
    }
    public function testGetUrlPurchaseBook() {
        $isbn = 12458;
        $category = 'PROMISH';
        $reg = '7';
        $this->bookExtension->getUrlPurchaseBook($isbn,$reg,$category);
        $this->bookExtension->getUrlPurchaseBook($isbn,4,$category);
        $this->bookExtension->getUrlPurchaseBook($isbn,11,$category);
        $this->bookExtension->getUrlPurchaseBook($isbn,12,$category);
        $this->bookExtension->getUrlPurchaseBook($isbn,6,$category);
        $this->bookExtension->getUrlPurchaseBook($isbn,1,$category);
        $this->bookExtension->getUrlPurchaseBook($isbn,0,$category);
        $category2 = 'AROMISH';
        $this->bookExtension->getUrlPurchaseBook($isbn,$reg,$category2);
    }
    public function testGetCategoryByType() {
        $categories  = array('PROMISH'=> array('category'=>'SH'),'PROMISI'=> array('category'=>'ST'));
        $this->bookExtension->getCategoryByType($categories);
    }
}

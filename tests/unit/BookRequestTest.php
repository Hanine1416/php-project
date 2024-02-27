<?php

use Doctrine\Common\Collections\ArrayCollection;
use MainBundle\Entity\BookRequest;

class BookRequestTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    /**
     * @var $bookRequest BookRequest
     */
    protected $bookRequest;
    
    protected function _before()
    {
        $this->bookRequest = new BookRequest();
    }

    protected function _after()
    {
    }

    /** Test book request isbn getter and setter */
    public function testBookIsbnGetterSetter()
    {
        $this->bookRequest->setBookIsbn('787989898');
        $this->assertNotEquals('77777', $this->bookRequest->getBookIsbn());
    }
    /** Test book request book format getter and setter */
    public function testBookRequestBookFormatGetterSetter()
    {
        $this->bookRequest->setBookFormat('Digital');
        $this->assertNotEquals('Print', $this->bookRequest->getBookFormat());
        $this->assertEquals('Digital', $this->bookRequest->getBookFormat());
    }
    /** Test book request address id getter and setter */
    public function testBookRequestAddressId()
    {
        $this->bookRequest->setAddressId('1');
        $this->assertNotEquals('3', $this->bookRequest->getAddressId());
        $this->assertEquals('1', $this->bookRequest->getAddressId());
    }
    /** Test book request pre-order getter and setter */
    public function testBookRequestPreoder()
    {
        $this->assertEquals(false, $this->bookRequest->isPreOrder());
        $this->bookRequest->setPreOrder(true);
        $this->assertEquals(true, $this->bookRequest->isPreOrder());
    }
    /** Test book request institutions getter and setter */
    public function testBookRequestInstitutions()
    {
        $institutions = new ArrayCollection();
        $this->assertNotNull($institutions);
        $this->bookRequest->setInstitutions($institutions);
        $this->assertEquals($institutions, $this->bookRequest->getInstitutions());
    }
}

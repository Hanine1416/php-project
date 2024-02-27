<?php

namespace tests\unit;

use MainBundle\Entity\BooksNew;

class BooksNewTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    /**
     * @var $booksnew   BooksNew
     */
    protected $booksNew;

    protected function _before()
    {
        $this->booksNew = new BooksNew('en');
    }

    protected function _after()
    {
    }

    /**  Test booksNew id getter and setter  */
    public function testBooksNewIdGetterSetter()
    {
        $this->booksNew->setId(1);
        $this->assertNotNull($this->booksNew->getId());
        $this->assertEquals(1, $this->booksNew->getId());
    }

    /**  Test booksNew language getter and setter  */
    public function testBooksNewLanguageGetterSetter()
    {
        $this->assertNotNull($this->booksNew->getLanguage());
        $this->assertEquals('en', $this->booksNew->getLanguage());
        $this->booksNew->setLanguage('es');
        $this->assertNotEquals('en', $this->booksNew->getLanguage());
        $this->assertEquals('es', $this->booksNew->getLanguage());
    }

    /**  Test booksNew edit date getter and setter  */
    public function testBooksNewEditDateGetterSetter()
    {
        $this->assertNotNull($this->booksNew->getEditDate());
        $newDate = new \DateTime('now');
        $this->booksNew->setEditDate($newDate);
        $this->assertNotNull($this->booksNew->getEditDate());
        $this->assertEquals($newDate, $this->booksNew->getEditDate());
    }

    /**  Test booksNew position getter and setter  */
    public function testBooksNewPositionGetterSetter()
    {
        $this->booksNew->setPosition(2);
        $this->assertNotNull($this->booksNew->getPosition());
        $this->assertNotEquals(4, $this->booksNew->getPosition());
        $this->assertEquals(2, $this->booksNew->getPosition());
    }

    /**  Test booksNew ISBN getter and setter  */
    public function testBooksNewIsbnGetterSetter()
    {
        $this->booksNew->setIsbn('9782294770517');
        $this->assertNotNull($this->booksNew->getIsbn());
        $this->assertNotEquals('9782294776953', $this->booksNew->getIsbn());
        $this->assertEquals('9782294770517', $this->booksNew->getIsbn());
    }
}

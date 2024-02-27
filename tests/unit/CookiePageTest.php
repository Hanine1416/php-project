<?php

use MBComponents\Helpers\TokenGenerator;
use UserBundle\Entity\CookiePage;

class CookiePageTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var $cookie CookiePage
     */
    protected $cookie;

    protected function _before()
    {
        $this->cookie = new  CookiePage('en');
    }

    protected function _after()
    {
    }

    /** Test cookie page id getter and setter */
    public function testCookiePageIdGetterSetter()
    {
        $this->cookie->setId(2);
        $this->assertNotEquals(1, $this->cookie->getId());
        $this->assertEquals(2, $this->cookie->getId());
    }
    /** Test cookie page token getter and setter */
    public function testCookiePageTokenGetterSetter()
    {
        $firstToken = $this->cookie->getToken();
        $this->assertNotNull($firstToken);
        $token = TokenGenerator::generateToken();
        $this->cookie->setToken($token);
        $this->assertNotEquals($firstToken, $this->cookie->getToken());
        $this->assertEquals($token, $this->cookie->getToken());
    }
    /** Test cookie page language getter and setter */
    public function testCookiePageLanguageGetterSetter()
    {
        $this->assertEquals('en', $this->cookie->getLanguage());
        $this->cookie->setLanguage('es');
        $this->assertNotEquals('en', $this->cookie->getLanguage());
        $this->assertEquals('es', $this->cookie->getLanguage());
    }
    /** Test cookie page bottom section getter and setter */
    public function testCookiePageBottomSectionGetterSetter()
    {
        $this->cookie->setBottomSection('Bottom section');
        $this->assertNotEquals('Top section', $this->cookie->getBottomSection());
        $this->assertEquals('Bottom section', $this->cookie->getBottomSection());
    }
    /** Test cookie page top section getter and setter */
    public function testCookiePageTopSectionGetterSetter()
    {
        $this->cookie->setTopSection('Top section');
        $this->assertNotEquals('Bottom section', $this->cookie->getTopSection());
        $this->assertEquals('Top section', $this->cookie->getTopSection());
    }
    /** Test cookie page table elements getter and setter */
    public function testCookiePageTableElementsGetterSetter()
    {
        $table = ['element 1', 'element 2'];
        $this->cookie->setTableElements($table);
        $this->assertCount(2, $this->cookie->getTableElements());
        $this->assertContains('element 1', $this->cookie->getTableElements());
    }
}

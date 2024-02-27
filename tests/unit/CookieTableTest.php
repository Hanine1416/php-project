<?php

use MBComponents\Helpers\TokenGenerator;
use UserBundle\Entity\CookieTable;

class CookieTableTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    /**
     * @var $cookieTable CookieTable
     */
    protected $cookieTable;
    
    protected function _before()
    {
        $this->cookieTable= new CookieTable();
    }

    protected function _after()
    {
    }

    /** Test cookie table id getter and setter */
    public function testCookieTableIdGetterSetter()
    {
        $this->cookieTable->setId(1);
        $this->assertEquals(1, $this->cookieTable->getId());
    }
    /** Test cookie table language getter and setter */
    public function testCookieTableLanguageGetterSetter()
    {
        $this->cookieTable->setLanguage('en');
        $this->assertEquals('en', $this->cookieTable->getLanguage());
    }
    /** Test cookie table description getter and setter */
    public function testCookieTableDescriptionGetterSetter()
    {
        $this->cookieTable->setDescription('description text');
        $this->assertEquals('description text', $this->cookieTable->getDescription());
    }
    /** Test cookie table enabled getter and setter */
    public function testCookieTableEnableGetterSetter()
    {
        $this->assertNotNull($this->cookieTable->isEnable());
        $this->assertEquals(true, $this->cookieTable->isEnable());
        $this->cookieTable->setEnable(false);
        $this->assertEquals(false, $this->cookieTable->isEnable());
    }
    /** Test cookie table id getter and setter */
    public function testCookieTableTokenGetterSetter()
    {
        $firstToken = $this->cookieTable->getToken();
        $this->assertNotNull($firstToken);
    }
    /** Test cookie table position getter and setter */
    public function testCookieTablePositionGetterSetter()
    {
        $this->cookieTable->setPosition(125);
        $this->assertEquals(125, $this->cookieTable->getPosition());
    }
    /** Test cookie table more info getter and setter */
    public function testCookieTableMoreInfoGetterSetter()
    {
        $this->cookieTable->setMoreInfo('More info');
        $this->assertEquals('More info', $this->cookieTable->getMoreInfo());
    }
    /** Test cookie table service name getter and setter */
    public function testCookieTableServiceNameGetterSetter()
    {
        $this->cookieTable->setServiceName('Service name');
        $this->assertEquals('Service name', $this->cookieTable->getServiceName());
    }
    /** Test cookie table cookie names getter and setter */
    public function testCookieTableCookieNamesGetterSetter()
    {
        $cookieNames = ['cookie1','cookie2','cookie3'];
        $this->cookieTable->setCookieNames($cookieNames);
        $this->assertCount(3, $this->cookieTable->getCookieNames());
        $this->assertEquals($cookieNames, $this->cookieTable->getCookieNames());
        $this->assertContains('cookie2', $this->cookieTable->getCookieNames());
    }
}

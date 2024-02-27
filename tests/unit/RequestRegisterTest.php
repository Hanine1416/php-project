<?php

use MBComponents\Helpers\TokenGenerator;

class RequestRegisterTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    /**
     * @var $register \UserBundle\Entity\RequestRegister
     */
    protected $requestRegister;

    protected function _before()
    {
        $this->requestRegister = new \UserBundle\Entity\RequestRegister();
    }

    protected function _after()
    {
    }

    /** Test request register id getter and setter */
    public function testRequestRegisterIdGetterSetter()
    {
        $this->requestRegister->setId(1);
        $this->assertNotEquals(2, $this->requestRegister->getId());
        $this->assertEquals(1, $this->requestRegister->getId());
    }

    /** Test request register email getter and setter */
    public function testRequestRegisterEmailGetterSetter()
    {
        $this->requestRegister->setEmail('user@user.com');
        $this->assertNotEquals('not true email', $this->requestRegister->getEmail());
        $this->assertEquals('user@user.com', $this->requestRegister->getEmail());
    }

    /** Test request register title getter and setter */
    public function testRequestRegisterTitleGetterSetter()
    {
        $this->requestRegister->setTitle('Dr');
        $this->assertNotEquals('Mr', $this->requestRegister->getTitle());
        $this->assertEquals('Dr', $this->requestRegister->getTitle());
    }

    /** Test request register firstName getter and setter */
    public function testRequestRegisterFirstNameGetterSetter()
    {
        $this->requestRegister->setFirstName('David');
        $this->assertNotEquals('Alex', $this->requestRegister->getFirstName());
        $this->assertEquals('David', $this->requestRegister->getFirstName());
    }

    /** Test request register middleName getter and setter */
    public function testRequestRegisterMiddleNameGetterSetter()
    {
        $this->requestRegister->setMiddleName('Stephan');
        $this->assertNotEquals('Alex', $this->requestRegister->getMiddleName());
    }

    /** Test request register lastName getter and setter */
    public function testRequestRegisterLastNameGetterSetter()
    {
        $this->requestRegister->setLastName('Ashton');
        $this->assertNotEquals('Renault', $this->requestRegister->getLastName());
    }

    /** Test request register get Url getter and setter */
    public function testRequestRegisterGetUrlGetterSetter()
    {
        $this->requestRegister->setUrl('http://www.fakeAddress.com');
        $this->assertNotEquals('http://www.wrongAddress.fr', $this->requestRegister->getUrl());
    }

    /** Test request register my IC guide getter and setter */
    public function testRequestRegisterMyIcGuideGetterSetter()
    {
        $this->requestRegister->setMyIcGuide('first request');
        $this->assertNotEquals('first login', $this->requestRegister->getMyIcGuide());
    }

    /** Test request register requested at getter and setter */
    public function testRequestRegisterRequestedAtGetterSetter()
    {
        $this->assertNotNull($this->requestRegister->getRequestedAt());
        $date = new DateTime('now');
        $this->requestRegister->setRequestedAt($date);
        $this->assertEquals($date, $this->requestRegister->getRequestedAt());
    }

    /** Test request register password getter and setter */
    public function testRequestRegisterPasswordGetterSetter()
    {
        $this->requestRegister->setPassword('password');
        $this->assertNotEquals('pwd', $this->requestRegister->getPassword());
        $this->assertEquals('password', $this->requestRegister->getPassword());
    }

    /** Test request register accept marketing getter and setter */
    public function testRequestRegisterAcceptMarketingGetterSetter()
    {
        $this->assertEquals(false, $this->requestRegister->isAcceptMarketing());
        $this->requestRegister->setAcceptMarketing(true);
        $this->assertEquals(true, $this->requestRegister->isAcceptMarketing());
    }

    /** Test request register token getter and setter */
    public function testRequestRegisterTokenGetterSetter()
    {
        $firstToken = $this->requestRegister->getToken();
        $this->assertNotNull($firstToken);
        $token = TokenGenerator::generateToken();
        $this->requestRegister->setToken($token);
        $this->assertNotEquals($firstToken, $this->requestRegister->getToken());
        $this->assertEquals($token, $this->requestRegister->getToken());
    }
}

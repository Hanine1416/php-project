<?php

use MBComponents\Helpers\TokenGenerator;
use UserBundle\Entity\ResetPasswordRequest;

class ResetPasswordTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    /**
     * @var $completeRegister ResetPasswordRequest;
     */
    protected $resetPassword;
    
    protected function _before()
    {
        $this->resetPassword= new ResetPasswordRequest('unit@test.com');
    }

    protected function _after()
    {
    }

    /** Test reset password id getter and setter */
    public function testResetPasswordIdGetterSetter()
    {
        $this->resetPassword->setId(1);
        $this->assertNotEquals(2, $this->resetPassword->getId());
        $this->assertEquals(1, $this->resetPassword->getId());
    }
    /** Test reset password token getter and setter */
    public function testResetPasswordTokenGetterSetter()
    {
        $firstToken = $this->resetPassword->getToken();
        $this->assertNotNull($firstToken);
        $token = TokenGenerator::generateToken();
        $this->resetPassword->setToken($token);
        $this->assertNotEquals($firstToken, $this->resetPassword->getToken());
        $this->assertEquals($token, $this->resetPassword->getToken());
    }
    /** Test reset password requested at getter and setter */
    public function testResetPasswordRequestedAtGetterSetter()
    {
        $this->assertNotNull($this->resetPassword->getRequestedAt());
        $date = new DateTime('now');
        $this->resetPassword->setRequestedAt($date);
        $this->assertEquals($date, $this->resetPassword->getRequestedAt());
    }
    /** Test reset password email getter and setter */
    public function testResetPasswordEmailGetterSetter()
    {
        $newEmail= 'new@email.com';
        $this->assertNotNull($this->resetPassword->getEmail());
        $this->assertEquals('unit@test.com', $this->resetPassword->getEmail());
        $this->resetPassword->setEmail($newEmail);
        $this->assertNotNull('unit@test.com', $this->resetPassword->getEmail());
        $this->assertEquals($newEmail, $this->resetPassword->getEmail());
    }
    /** Test reset password enabled getter and setter */
    public function testResetPasswordEnabledGetterSetter()
    {
        $this->assertEquals(true, $this->resetPassword->isEnabled());
        $this->resetPassword->setEnabled(false);
        $this->assertEquals(false, $this->resetPassword->isEnabled());
    }
    /** Test reset password redirect profile getter and setter */
    public function testResetPasswordRedirectProfileGetterSetter()
    {
        $this->assertEquals(false, $this->resetPassword->isRedirectProfile());
        $this->resetPassword->setRedirectProfile(true);
        $this->assertEquals(true, $this->resetPassword->isRedirectProfile());
    }
    /** Test reset password user identifier getter and setter */
    public function testResetPasswordUserIdentifierGetterSetter()
    {
        $this->resetPassword->setUserIdentifier('User identifier');
        $this->assertNotEquals('Identifier', $this->resetPassword->getUserIdentifier());
    }
}

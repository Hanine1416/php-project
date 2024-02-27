<?php

use UserBundle\Entity\Admin;

class AdminTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    
    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testAdminGetterSetter()
    {
        $admin = new Admin();
        /** Set and get admin id */
        $admin->setId(1);
        $this->assertEquals(1, $admin->getId());
        /** Set and get admin password */
        $admin->setPassword('password');
        $this->assertEquals('password', $admin->getPassword());
        /** Set and get admin username */
        $admin->setUsername('admin');
        $this->assertNotEquals('not admin', $admin->getUsername());
        /**  Set and get admin registration date*/
        $registrationDate = new DateTime('now');
        $admin->setRegistrationDate($registrationDate);
        $this->assertEquals($registrationDate, $admin->getRegistrationDate());
    }
}

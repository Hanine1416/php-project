<?php

use MBComponents\Test\AppTester;
use UserBundle\Entity\User;
use UserBundle\Services\UserService;

class UserServiceTest extends AppTester
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    /** @var  UserService $userService */
    protected $userService;
    /** @var User $user */
    protected $user;

    protected function _before()
    {
        $this->user = new User();
    }

    public function __construct(?string $name = null, array $data = array(), string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->userService = new UserService($this->app->sfContainer);
    }

//    public function testGetUser(){
//        $this->assertNull($this->userService->getUser());
//    }
    public function testUserService() {
        $this->userService->authenticate(AppTester::UNIT_TEST_EMAIL);
        $this->userService->getUserCountryCode('United states');
        $this->userService->getUserCodeRegion($this->user);
    }

}

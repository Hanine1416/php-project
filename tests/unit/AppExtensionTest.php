<?php

use MBComponents\Test\AppTester;
use MBComponents\Twig\Extensions\AppExtension;

class AppExtensionTest extends AppTester
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /** @var AppExtension */
    protected $appExtension;

    public function __construct(?string $name = null, array $data = array(), string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->appExtension = new AppExtension($this->app);
    }

    public function testArrayToString()
    {
        $array = [
            'isbn' => '123456',
            'details' => [
                'author' => 'maoyan',
                'description' => 'no content'
            ]
        ];
        $this->assertEquals('<p>isbn: 123456</p><p>author: maoyan</p><p>description: no content</p>', $this->appExtension->arrayToString($array));
    }

//    public function testGetLoggedInUser(){
//        $this->assertNull($this->appExtension->getLoggedInUser());
//        /** @var UserService $userService */
//        $userService = $this->app->getService(\MBComponents\Services\SLX::class)->getUserService();
//        $userService->authenticate(AppTester::UNIT_TEST_EMAIL);
//        $this->assertEquals(AppTester::UNIT_TEST_EMAIL,$this->appExtension->getLoggedInUser()->getEmail());
//    }

    public function testGenerateUrl()
    {
        $this->assertContains('register', $this->appExtension->generateUrl('register'));
    }

//    public function testLoggedIn(){
//        $this->app->session->remove('userInfo');
//        $this->assertFalse($this->appExtension->isLoggedIn());
//        /** @var UserService $userService */
//        $userService = $this->app->getService(\MBComponents\Services\SLX::class)->getUserService();
//        $userService->authenticate(AppTester::UNIT_TEST_EMAIL);
//        $this->assertTrue($this->appExtension->isLoggedIn());
//    }

    public function TestOverrideAsset()
    {
        $assetPath = 'http://inspectioncopy.elsevier.com/assert/layout.css';
        $this->assertContains('?v=', $this->appExtension->overrideAsset($assetPath));
        $this->appExtension->overrideAsset($assetPath);
    }

    public function testDynamicReplace()
    {
        $chaine = 'Hello _username_ welcome back';
        $this->assertEquals('Hello user welcome back', $this->appExtension->dynamicReplace('_username_', 'user', $chaine));
    }
    public function testJsonDecode()
    {
        $json = '{"username":"unit_test@mobelite.fr","role":"user"}';
        $object = new stdClass();
        $object->username = "unit_test@mobelite.fr";
        $object->role = "user";
        $this->assertEquals($object, $this->appExtension->jsonDecode($json));
    }
    public function testLangSiteDateFormat()
    {
        $date = '2019-06-25';
        $this->assertEquals('25 June 2019', $this->appExtension->langSiteDateFormat($date));
    }
    public function testGetIpDate() {
       $this->appExtension->getIpDate();
    }
    public function testGetClientIpServer() {
        $this->appExtension->getClientIpServer();
    }

    public function testFormatString() {
        $var = "test";
        $this->appExtension->formatString($var);
    }

    public function testFixCoverLinkFr() {
        $link = 'Etudes de médecine';
        $this->appExtension->fixCoverLinkFr($link);
    }

}

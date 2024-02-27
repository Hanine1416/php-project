<?php

use MBComponents\Services\SlxWebService;
use MBComponents\Test\AppTester;

class SlxWebServiceTest extends AppTester
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    /** @var  SlxWebService $userService */
    protected $slxService;

    public function __construct(?string $name = null, array $data = array(), string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->slxService = new SlxWebService($this->app->sfContainer);
    }

    public function testGetCities(){
        $this->assertNotEmpty($this->slxService->getCities('France'));
        $this->assertEmpty($this->slxService->getCities('LOREMIPSUM'));
    }

//    public function testGetLanguageRegionFromCountry(): void
//    {
//        $this->assertEquals(['region'=>11,'language'=>'fr'],$this->slxService->getLanguageRegionFromCountry('FR'));
//        $this->assertEquals(['region'=>11,'language'=>'fr'],$this->slxService->getLanguageRegionFromCountry('France'));
//        $this->assertEquals(['region'=>6,'language'=>'es'],$this->slxService->getLanguageRegionFromCountry('ES'));
//        $this->assertEquals(['region'=>7,'language'=>'en'],$this->slxService->getLanguageRegionFromCountry('LOREMIPSUM'));
//    }

}

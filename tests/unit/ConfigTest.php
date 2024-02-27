<?php

use lib\Config;

class ConfigTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    /**
     * @var Config $config
     */
    protected $config;

    public static $confArray=[];


    protected function _before()
    {
        $this->config = new Config();
    }

    /** Test Read function of config */
    public function testReadConfig()
    {
        $this->assertEquals(['siteId'=>''], ['siteId'=> Config::read('siteId')]);
    }

    /** Test Write function of config */
    public function testWriteConfig()
    {
        Config::write('key', 'applicationKey');
        $this->assertEquals(['key'=>'applicationKey'], ['key'=> Config::read('key')]);
    }
    /** Test Write function of config */
    public function testKeyConfig()
    {
        Config::has('key');
        $this->assertEquals(true, Config::has('key'));
    }
}

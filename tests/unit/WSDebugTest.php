<?php

use MainBundle\Entity\WSDebug;

class WSDebugTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    /** @var WSDebug $user */
    protected $wsdebug;

    public static $IDENTIFIER = 'identifier';

    protected function _before()
    {
        $this->wsdebug = new WSDebug(WSDebugTest::$IDENTIFIER);
    }

    /** Test identifier */
    public function testIdentifier(){
        $this->assertEquals(WSDebugTest::$IDENTIFIER,$this->wsdebug->getIdentifier());
    }

    /** Test response */
    public function testResponse(){
        $response = 'response';
        $this->wsdebug->setResponse($response);
        $this->assertEquals($response,$this->wsdebug->getResponse());
    }

    /** Test response */
    public function testQuery(){
        $query = ['query'=>'query value'];
        $this->wsdebug->setQuery($query);
        $this->assertArrayHasKey('query',$this->wsdebug->getQuery());
        $this->assertEquals($query,$this->wsdebug->getQuery());
    }
}

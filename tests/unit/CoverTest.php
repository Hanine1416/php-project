<?php

use MainBundle\Entity\Cover;

class CoverTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    /**
     * @var $cover Cover
     */
    protected $cover;
    
    protected function _before()
    {
        $this->cover= new Cover('en');
    }

    protected function _after()
    {
    }

    /**  Test cover id getter and setter  */
    public function testCoverIdGetterSetter()
    {
        $this->cover->setId(1);
        $this->assertNotNull($this->cover->getId());
        $this->assertEquals(1, $this->cover->getId());
    }
    /**  Test cover image getter and setter  */
    public function testCoverImageGetterSetter()
    {
        $this->cover->setImage('Image 2');
        $this->assertNotNull($this->cover->getImage());
        $this->assertNotEquals('Image 1', $this->cover->getImage());
        $this->assertEquals('Image 2', $this->cover->getImage());
    }
    /**  Test cover language getter and setter  */
    public function testCoverLanguageGetterSetter()
    {
        $this->assertNotNull($this->cover->getLanguage());
        $this->assertEquals('en', $this->cover->getLanguage());
        $this->cover->setLanguage('es');
        $this->assertNotEquals('en', $this->cover->getLanguage());
        $this->assertEquals('es', $this->cover->getLanguage());
    }
    /**  Test cover profession getter and setter  */
    public function testCoverProfessionGetterSetter()
    {
        $this->assertNull($this->cover->getProfession());
        $this->cover->setProfession('Dentistry');
        $this->assertNotNull($this->cover->getProfession());
        $this->assertNotEquals('Medicine', $this->cover->getProfession());
        $this->assertEquals('Dentistry', $this->cover->getProfession());
    }
    /**  Test cover edit date getter and setter  */
    public function testCoverEditDateGetterSetter()
    {
        $this->assertNotNull($this->cover->getEditDate());
        $newDate = new DateTime('now');
        $this->cover->setEditDate($newDate);
        $this->assertNotNull($this->cover->getEditDate());
        $this->assertEquals($newDate, $this->cover->getEditDate());
    }
    /**  Test cover position getter and setter  */
    public function testCoverPositionGetterSetter()
    {
        $this->cover->setPosition(750);
        $this->assertNotNull($this->cover->getPosition());
        $this->assertNotEquals(200, $this->cover->getPosition());
        $this->assertEquals(750, $this->cover->getPosition());
    }
    /**  Test cover Category getter and setter  */
    public function testCoverCategoryGetterSetter()
    {
        $category = $this->cover->setCategory("hs");
        $this->assertNotNull($this->cover->getCategory());
        $this->assertNotEquals($category, $this->cover->getCategory());
    }
}

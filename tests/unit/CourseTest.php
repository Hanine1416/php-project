<?php

use MainBundle\Entity\Course;

class CourseTest extends \Codeception\Test\Unit
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
    public function testUserCourseGetterSetter()
    {
        /** @var $course */
        $course= new Course();
        /** Set and get course id */
        $course->setId(1);
        $this->assertNotEquals(2, $course->getId());
        /** Set and get course itemId */
        $course->setItemId(3);
        $this->assertNotEquals(2, $course->getItemId());
        /** Set and get course pickList id  */
        $course->setPicklistId('78');
        $this->assertNotEquals('87', $course->getPicklistId());
        /** Set and get course  */
        $course->setShortText('shortText');
        $this->assertEquals('shortText', $course->getShortText());
        /** Set and get course text */
        $course->setText('This is course text');
        $this->assertNotEquals('false text', $course->getText());
    }
}

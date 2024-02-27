<?php

use MainBundle\Entity\BookInstitutionRequest;

class BookInstitutionRequestTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    /**
     * @var $bookInstitutionRequest BookInstitutionRequest
     */
    protected $bookInstitutionRequest;
    
    protected function _before()
    {
        $this->bookInstitutionRequest= new BookInstitutionRequest();
    }

    protected function _after()
    {
    }

    /** Test request book institution inst id getter and setter */
    public function testBookInstitutionRequestInstitutionIdGetterSetter()
    {
        $this->bookInstitutionRequest->setInstitutionId('1');
        $this->assertEquals('1', $this->bookInstitutionRequest->getInstitutionId());
    }
    /** Test request institution course start date getter and setter */
    public function testBookInstitutionRequestBookUsedReasonGetterSetter()
    {
        $this->bookInstitutionRequest->setBookUsedReason('Book Used Reason');
        $this->assertEquals('Book Used Reason', $this->bookInstitutionRequest->getBookUsedReason());
    }
    /** Test request book institution current used book getter and setter */
    public function testBookInstitutionRequestCurrentUsedBookGetterSetter()
    {
        $this->bookInstitutionRequest->setCurrentUsedBook('Current used book name');
        $this->assertEquals('Current used book name', $this->bookInstitutionRequest->getCurrentUsedBook());
    }
    /** Test request book institution course getter and setter */
    public function testBookInstitutionRequestCourseGetterSetter()
    {
        $this->bookInstitutionRequest->setCourse('Course');
        $this->assertNotEquals('Course name', $this->bookInstitutionRequest->getCourse());
        $this->assertEquals('Course', $this->bookInstitutionRequest->getCourse());
    }
    /** Test request book institution course code getter and setter */
    public function testBookInstitutionRequestCourseCodeGetterSetter()
    {
        $this->bookInstitutionRequest->setCourseCode('Course Code');
        $this->assertNotEquals('Course ', $this->bookInstitutionRequest->getCourseCode());
        $this->assertEquals('Course Code', $this->bookInstitutionRequest->getCourseCode());
    }
    /** Test request book institution course name getter and setter */
    public function testBookInstitutionRequestCourseNameGetterSetter()
    {
        $this->bookInstitutionRequest->setCourseName('Course name');
        $this->assertNotEquals('Course', $this->bookInstitutionRequest->getCourseName());
        $this->assertEquals('Course name', $this->bookInstitutionRequest->getCourseName());
    }
    /** Test request book institution course level getter and setter */
    public function testBookInstitutionRequestCourseLevelGetterSetter()
    {
        $this->bookInstitutionRequest->setCourseLevel('Undergraduate');
        $this->assertNotEquals('Level 1', $this->bookInstitutionRequest->getCourseLevel());
        $this->assertEquals('Undergraduate', $this->bookInstitutionRequest->getCourseLevel());
    }
    /** Test request book institution rec level getter and setter */
    public function testBookInstitutionRequestRecLevelGetterSetter()
    {
        $this->bookInstitutionRequest->setRecLevel('Rec Level');
        $this->assertNotEquals('Rec', $this->bookInstitutionRequest->getRecLevel());
        $this->assertEquals('Rec Level', $this->bookInstitutionRequest->getRecLevel());
    }
    /** Test request book institution students number getter and setter */
    public function testBookInstitutionRequestStudentNumberGetterSetter()
    {
        $this->bookInstitutionRequest->setStudentsNumber(55);
        $this->assertNotEquals(50, $this->bookInstitutionRequest->getStudentsNumber());
        $this->assertEquals(55, $this->bookInstitutionRequest->getStudentsNumber());
    }
    /** Test request book institution start date getter and setter */
    public function testBookInstitutionRequestStartDateGetterSetter()
    {
        $this->bookInstitutionRequest->setStartDate('01-01-2020');
        $this->assertNotEquals('01-02-2020', $this->bookInstitutionRequest->getStartDate());
    }
    /** Test request book institution end date getter and setter */
    public function testBookInstitutionRequestEndDateGetterSetter()
    {
        $this->bookInstitutionRequest->setEndDate('01-01-2020');
        $this->assertNotEquals('01-02-2020', $this->bookInstitutionRequest->getEndDate());
    }
}

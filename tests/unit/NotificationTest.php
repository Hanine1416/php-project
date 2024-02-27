<?php

use UserBundle\Entity\Notification;

class NotificationTest extends \Codeception\Test\Unit
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

    /** Test notification getters and setters */
    public function testUserNotificationGetterSetter()
    {
        $notification = new Notification();
        /** Get and set notification title  */
        $notification->setTitle('Book Title');
        $this->assertEquals('Book Title', $notification->getTitle());
        /** Get and set notification IsRead  */
        $notification->setIsRead(false);
        $this->assertNotEquals(true, $notification->getIsRead());
        /** Get and set notification Type  */
        $notification->setEventType('DigitalApproved');
        $this->assertEquals('DigitalApproved', $notification->getEventType());
        /** Get and set notification isbn  */
        $notification->setIsbn('9780702071041');
        $this->assertNotEquals('9780702071025', $notification->getIsbn());
        /** Get and set notification link  */
        $notification->setLink('link');
        $this->assertEquals('link', $notification->getLink());
        /** Get and set notification date  */
        $todayDate = new DateTime('now');
        $notification->setDate($todayDate);
        $this->assertEquals($todayDate, $notification->getDate());
    }
}

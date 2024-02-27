<?php

use MainBundle\Entity\Contact;

class ContactTest extends \Codeception\Test\Unit
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

    public function testContactGetterSetter()
    {
        $contact = new Contact();
        /** Get and Set name */
        $contact->setName('Name');
        $this->assertEquals('Name', $contact->getName());
        /** Get and Set contact subject */
        $contact->setSubject('Subject');
        $this->assertEquals('Subject', $contact->getSubject());
        /** Get and Set contact description */
        $contact->setDescription('description here');
        $this->assertNotEquals('description', $contact->getDescription());
        /** Set and get contact phone */
        $contact->setPhone('78995656');
        $this->assertNotEquals('789', $contact->getPhone());
        /** Set and get contact email */
        $contact->setEmail('email@email.com');
        $this->assertEquals('email@email.com', $contact->getEmail());
        /**  Set and get contact country */
        $contact->setCountry('Country');
        $this->assertEquals('Country', $contact->getCountry());
        /** Set and get contact institution */
        $contact->setInstitution('Institution name');
        $this->assertNotEquals('Institution', $contact->getInstitution());
    }
}

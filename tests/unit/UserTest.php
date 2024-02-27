<?php

use Doctrine\Common\Collections\ArrayCollection;
use UserBundle\Entity\Address;
use UserBundle\Entity\Institution;
use UserBundle\Entity\User;

class UserTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    /** @var User $user */
    protected $user;

    protected function _before()
    {
        $this->user = new User();
    }

    protected function _after()
    {
    }

    /** Test create user getter and setter */
    public function testUserCreateUser()
    {
        $this->user->setCreateUser('user');
        $this->assertEquals('user', $this->user->getCreateUser());
    }

    /** Test user id getter and setter */
    public function testUserIdUser()
    {
        $this->user->setUserId(1);
        $this->assertEquals(1, $this->user->getUserId());
    }

    /** Test user first name getter and setter */
    public function testUserFirstName()
    {
        $this->user->setFirstName('firstname');
        $this->assertNotEquals(null, $this->user->getFirstName());
        $this->assertNotEquals('nameeeee', $this->user->getFirstName());
        $this->assertEquals('firstname', $this->user->getFirstName());
    }

    /** Test user middle name getter and setter */
    public function testUserMiddleName()
    {
        $this->user->setMiddleName('middlename');
        $this->assertEquals('middlename', $this->user->getMiddleName());
    }

    /** Test user last name getter and setter*/
    public function testUserLastName()
    {
        $this->user->setLastName('lastname');
        $this->assertEquals('lastname', $this->user->getLastName());
    }

    /** Test user full name getter and setter*/
    public function testUserFullName()
    {
        $this->user->setFirstName('firstname');
        $this->user->setLastName('lastname');
        $this->assertEquals('firstname lastname', $this->user->getFullName());
    }

    /** Test user password getter and setter*/
    public function testUserPassword()
    {
        $this->user->setPassword('password');
        $this->assertEquals('password', $this->user->getPassword());
    }

    /** Test user email getter and setter*/
    public function testUserEmail()
    {
        $this->user->setEmail('user@email.com');
        $this->assertEquals('user@email.com', $this->user->getEmail());
    }

    /** Test user mobile number getter and setter*/
    public function testUserMobile()
    {
        $userMobile = 99999999;
        $this->user->setMobile($userMobile);
        $this->assertEquals($userMobile, $this->user->getMobile());
    }

    /** Test user mainPhone getter and setter*/
    public function testUserMainPhone()
    {
        $userMainPhone = 11111111;
        $this->user->setMainPhone($userMainPhone);
        $this->assertEquals($userMainPhone, $this->user->getMainPhone());
    }

    /** Test user amName getter and setter*/
    public function testUserAmName()
    {
        $this->user->setAmName('amNameUser');
        $this->assertEquals('amNameUser', $this->user->getAmName());
    }

    /** Test user amEmail getter and setter*/
    public function testUserAmEmail()
    {
        $this->user->setAmEmail('user@amEmail.com');
        $this->assertEquals('user@amEmail.com', $this->user->getAmEmail());
    }

    /** Test user amPhone getter and setter*/
    public function testUserAmPhone()
    {
        $userAmPhone = 11111111;
        $this->user->setAmPhone($userAmPhone);
        $this->assertEquals($userAmPhone, $this->user->getAmPhone());
    }

    /** Test user country getter and setter*/
    public function testUserCountry()
    {
        $countryName = 'United Kingdom';
        $country = new stdClass();
        $country->Text = $countryName;
        $this->user->setCountry($countryName);
        $this->assertEquals($countryName, $this->user->getCountry());
        $this->user->setCountry($country);
        $this->assertEquals($countryName, $this->user->getCountry());
    }

    /** Test user country getter and setter*/
    public function testUserTitle()
    {
        $this->user->setTitle('title');
        $this->assertEquals('title', $this->user->getTitle());
    }

    /** Test user country getter and setter*/
    public function testUserSuffix()
    {
        $this->user->setSuffix('Mr');
        $this->assertEquals('Mr', $this->user->getSuffix());
    }

    /** Test user accept marketing getter and setter*/
    public function testUserAcceptMarketing()
    {
        $this->user->setAcceptMarketing(true);
        $this->assertEquals(true, $this->user->isAcceptMarketing());
    }

    /** Test user cpf getter and setter*/
    public function testUserCpf()
    {
        $this->user->setCpf('78954');
        $this->assertEquals('78954', $this->user->getCpf());
    }

    /** Test user url getter and setter*/
    public function testUserUrl()
    {
        $this->user->setUrl('http://www.testURl.com');
        $this->assertEquals('http://www.testURl.com', $this->user->getUrl());
    }

    /** Test user stage setter*/
    public function testUserStage()
    {
        $this->user->setStage('stage');
        $this->assertEquals('stage', $this->user->getStage());
    }

    /** Test user webSite setter*/
    public function testUserWebSite()
    {
        $userSiteUrl = 'http://www.testURl.com';
        $this->user->setWebsite($userSiteUrl);
        $this->assertEquals($userSiteUrl,$this->user->getUrl());
    }

    /** Test user vatNumber setter*/
    public function testUserVatNumber()
    {
        $vatNumber = '12458442';
        $this->user->setVatNumber($vatNumber);
        $this->assertEquals($vatNumber,$this->user->getCpf());
    }

    /** Test user speciality setter*/
    public function testUserSpeciality()
    {
        $speciality = 'Medicine';
        $this->user->setSpecialty($speciality);
        $this->assertEquals($speciality,$this->user->getSpecialty());
    }

    public function testUserInstitution()
    {

        /** Change/Set the information of the user institution */
        $this->user->setInstitutionName('Institution Name');
        $this->user->setInstitutionId(11);
        $this->user->setDepartmentName('Department Name');
        $this->user->setDepartmentId(12);
        $this->user->setProfession('Profession');

        /** Test/Get the values updated */
        $this->assertEquals('Institution Name', $this->user->getInstitutionName());
        $this->assertEquals(11, $this->user->getInstitutionId());
        $this->assertEquals('Department Name', $this->user->getDepartmentName());
        $this->assertEquals(12, $this->user->getDepartmentId());
        $this->assertEquals('Profession', $this->user->getProfession());
        /** @var ArrayCollection $institutions */
        $institutions = new ArrayCollection();
        $institution1 = new Institution();
        $institution1->setEnabled(false);
        $institution2 = new Institution();
        $institution2->setEnabled(true);
        $institutions->add($institution1);
        $institutions->add($institution2);
        $this->user->setInstitutions($institutions);
        $institution3 = new Institution();
        $institution3->setEnabled(true);
        $this->user->addInstitution($institution3);
        $this->assertEquals($institution1,$this->user->getInstitutions()->last());
        $institution2->setEnabled(false);
        $this->assertEquals($institution1, $institution2);
    }

    public function testUserAddress(){
        $this->user->setAddresses(null);
        $address1= new Address();
        $address1->setType('Enabled');
        $address2= new Address();
        $address2->setType('Disabled');
        $addressCollection = new ArrayCollection();
        $addressCollection->add($address1);
        $addressCollection->add($address2);
        $this->user->setAddresses($addressCollection);
        $this->assertEquals($address2,$this->user->getAddresses()->last());
    }

    /** Test user version setter and getter*/
    public function testUserVersion()
    {
        $version = '3';
        $this->user->setIcProfileVersion($version);
        $this->assertEquals($version,$this->user->getIcProfileVersion());
    }
    /** Test user file setter and getter*/
    public function testUserFile()
    {
        $file = "Teacher.pdf";
        $this->user->setProfileFileProvided($file);
        $this->assertEquals($file,$this->user->getProfileFileProvided());
    }
    /** Test add address when address is empty*/
    public function testAddAddress()
    {
        $address = new Address();
        $this->user->addAddress($address);
    }

}

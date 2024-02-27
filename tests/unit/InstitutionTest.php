<?php
use UserBundle\Entity\Institution;
use UserBundle\Entity\User;

class InstitutionTest extends \Codeception\Test\Unit
{
    /**
     * @var UnitTester
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

    public function testUserInstitutionGetterSetter()
    {
        /** Institutions should initialized with array collection  */
        $this->assertNotNull($this->user->getInstitutions());
        /** Expect user primary institution to be null */
        $this->assertNull($this->user->getPrimaryInstitution());
        /** Initialise a not primary institution with id 1 */
        $institutionNotPrimary = new Institution();
        $institutionNotPrimary->setId(1);
        /** Initialise a primary institution with id 2  */
        $institutionPrimary = new Institution();
        $institutionPrimary->setId(2);
        $institutionPrimary->setIsPrimary(true);
        /** Set the primary institution disabled */
        $institutionPrimary->setEnabled(false);
        /** Add the user institutions */
        $this->user->addInstitution($institutionNotPrimary);
        $this->user->addInstitution($institutionPrimary);
        /** Set the user institutions with the new created ones */
        $this->user->setInstitutions($this->user->getInstitutions());
        $this->assertEquals(2, $this->user->getInstitutions()->count(), 'Expect user institution to be 2');
        $this->assertNotNull($this->user->getPrimaryInstitution());
        $this->assertEquals($institutionPrimary, $this->user->getPrimaryInstitution());
        $this->assertEquals(false, $institutionPrimary->isEnabled());
        /** Remove the institution with id 1 */
        $this->user->removeInstitution(1);
        $this->assertEquals(1, $this->user->getInstitutions()->count(), 'Expect user institution to be1 after deleting the first');
        /** Change/Set the information of the primary institution */
        $institutionPrimary->setInstitutionName('Institution Name');
        $institutionPrimary->setInstitutionId(11);
        $institutionPrimary->setDepartmentName('Department Name');
        $institutionPrimary->setDepartmentId(12);
        $institutionPrimary->setProfession('Profession');
        $institutionPrimary->setSpeciality('Specialty');
        $institutionPrimary->setHasRequest(true);
        /** Update the primary institution */
        $this->user->updateInstitution($institutionPrimary);
        /** Test/Get the values updated */
        $this->assertEquals('Institution Name', $this->user->getPrimaryInstitution()->getInstitutionName());
        $this->assertEquals(11, $this->user->getPrimaryInstitution()->getInstitutionId());
        $this->assertEquals('Department Name', $this->user->getPrimaryInstitution()->getDepartmentName());
        $this->assertEquals(12, $this->user->getPrimaryInstitution()->getDepartmentId());
        $this->assertEquals('Profession', $this->user->getPrimaryInstitution()->getProfession());
        $this->assertEquals('Specialty', $this->user->getPrimaryInstitution()->getSpeciality());
        $this->assertNotEquals('false', $this->user->getPrimaryInstitution()->isHasRequest());
    }
}

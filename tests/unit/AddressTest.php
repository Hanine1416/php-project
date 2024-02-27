<?php

use Doctrine\Common\Collections\ArrayCollection;
use UserBundle\Entity\Address;
use UserBundle\Entity\User;

class AddressTest extends \Codeception\Test\Unit
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

    // tests
    public function testUserAddressSetterGetter()
    {
        /** Addresses should initialized with array collection  */
        $this->assertNotNull($this->user->getAddresses());
        /** Initialise a not primary institution with id 1 */
        $newAddress = new Address();
        /**  check if address is not primary by default*/
        $this->assertEquals(false, $newAddress->isPrimary());
        /** Set address id */
        $newAddress->setId(1);
        $this->assertEquals(1, $newAddress->getId());
        /** Set address country */
        $newAddress->setCountry('country');
        $this->assertNotEquals('count', $newAddress->getCountry());
        /** Set address address1 */
        $newAddress->setAddress1('address1');
        $this->assertEquals('address1', $newAddress->getAddress1());
        /** Set address address2 */
        $newAddress->setAddress2('address2');
        $this->assertEquals('address2', $newAddress->getAddress2());
        /** Set address address3 */
        $newAddress->setAddress3('address3');
        $this->assertEquals('address3', $newAddress->getAddress3());
        /** Set address address4 */
        $newAddress->setAddress4('address4');
        $this->assertEquals('address4', $newAddress->getAddress4());
        /** Set address city */
        $newAddress->setCity('city');
        $this->assertEquals('city', $newAddress->getCity());
        /** Set address state */
        $newAddress->setState('state');
        $this->assertEquals('state', $newAddress->getState());
        /** Set address postal code */
        $newAddress->setPostalCode('45A');
        $this->assertEquals('45A', $newAddress->getPostalCode());
        /** Set address phone */
        $newAddress->setPhone('789656464');
        $this->assertEquals('789656464', $newAddress->getPhone());
        /** Set address description */
        $newAddress->setDescription('description');
        $this->assertEquals('description', $newAddress->getDescription());
        /** Set address salutation */
        $newAddress->setSalutation('salutation');
        $this->assertNotEquals('description', $newAddress->getSalutation());
        /** Set address type */
        $newAddress->setType('Disabled');
        $this->assertNotEquals('enabled', $newAddress->getType());
        /** Set and get address can be deleted */
        $newAddress->setCanBeDeleted(false);
        $this->assertNotEquals(true, $newAddress->getCanBeDeleted());
        /** Add new address to the user */
        $this->user->addAddress($newAddress);
        $this->assertEquals(1, $this->user->getAddresses()->count());
        /** Set and get address as primary */
        $newAddress->setIsPrimary(true);
        $this->assertEquals(true, $newAddress->isPrimary());
        /** Update address as primary */
        $this->user->updateAddress($newAddress);
        /** Create second address */
        $secondAddress= new Address();
        $secondAddress->setId(2);
        /** Set address type */
        $newAddress->setType('Disabled');
        $this->assertNotEquals('enabled', $newAddress->getType());
        $this->user->addAddress($secondAddress);
        /** Remove the second address and test if the number of addresses is equal to 1 after delete */
        $this->assertCount(2, $this->user->getAddresses());
        $this->user->removeAddress(2);
        $this->assertCount(1, $this->user->getAddresses());
        /** Set addresses as primary */
        $addresses = new ArrayCollection();
        $this->user->setAddresses($addresses);
        /** Set address country when address is an object*/
        $myAddress = new class {
            public $text = "France";
        };
        $myAddress->text = "France";
        $newAddress->setCountry($myAddress);
    }
}

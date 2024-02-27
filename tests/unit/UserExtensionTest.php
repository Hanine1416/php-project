<?php

use Doctrine\Common\Collections\ArrayCollection;
use MBComponents\Interfaces\CSRFToken;
use MBComponents\Test\AppTester;
use UserBundle\Entity\Institution;
use UserBundle\Entity\Banner;

class UserExtensionTest extends AppTester
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /** @var \MBComponents\Twig\Extensions\UserExtension */
    protected $userExtension;

    public function __construct(?string $name = null, array $data = array(), string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->userExtension = new \MBComponents\Twig\Extensions\UserExtension($this->app);
    }

    public function getUserExtensions(){
        $filters = $this->userExtension->getFilters();
        $functions = $this->userExtension->getFunctions();
        $this->assertNotNull($filters);
        $this->assertNotNull($functions);
    }

    public function testIsRRRecaptchaEnabled(){
        $this->assertFalse($this->userExtension->isRRRecaptchaEnabled());
        $currentTime = (new \DateTime())->getTimestamp();
        $this->app->session->set('register_attempts',[$currentTime - 100,$currentTime,$currentTime]);
        $this->assertTrue($this->userExtension->isRRRecaptchaEnabled());
    }

    public function testIsFPRecaptchaEnabled(){
        $this->assertFalse($this->userExtension->isFPRecaptchaEnabled());
        $currentTime = (new \DateTime())->getTimestamp();
        $this->app->session->set('fp_attempts',[$currentTime - 100,$currentTime,$currentTime]);
        $this->assertTrue($this->userExtension->isFPRecaptchaEnabled());
    }

    public function testGetInstitutionNotDisabledNumber(){
        $this->assertEquals(0,$this->userExtension->getInstitutionNotDisabledNumber(null));
        $institution1 = new Institution();
        $institution1->setEnabled(false);
        $institution2 = new Institution();
        $institution2->setEnabled(true);
        $institution3 = new Institution();
        $institution3->setEnabled(true);
        $institutionCollection = new ArrayCollection();
        $institutionCollection->add($institution1);
        $institutionCollection->add($institution2);
        $institutionCollection->add($institution3);
        $this->assertEquals(2,$this->userExtension->getInstitutionNotDisabledNumber($institutionCollection));
    }

    /*public function testGetNotification(){
        $this->app->session->remove('userInfo');
        $this->assertNull($this->userExtension->getNotifications());

        $userService = $this->app->getService(\MBComponents\Services\SLX::class)->getUserService();
        $userService->authenticate(AppTester::UNIT_TEST_EMAIL);
        $this->assertCount(0,$this->userExtension->getNotifications());
        $userService->removeUserFromSession();
    }*/

    public function testGetActiveBanner(){
        $ActiveBanner = new Banner();
        $ActiveBanner->setIsEnabled(true);
        $this->userExtension->getActiveBanner(6);
        $this->app->session->set('site-lang','es');
        $bannerEs = $this->userExtension->getActiveBanner(6);
        $this->assertNotNull($bannerEs);

        $this->app->session->set('site-lang','en');
        $bannerEn = $this->userExtension->getActiveBanner(7);
        $this->assertNotNull($bannerEn);

        $this->app->session->set('site-lang','fr');
        $bannerFr = $this->userExtension->getActiveBanner(11);
        $this->assertNotNull($bannerFr);

        $this->app->session->set('site-lang','de');
        $bannerDe = $this->userExtension->getActiveBanner(12);
        $this->assertNotNull($bannerDe);

    }

    public function testProfileUpdated(){
        $this->app->session->set('profile_updated',true);
        $this->userExtension->isProfileUpdate();
    }

    public function testTokenResetPassword(){
        $this->userExtension->tokenResetPassword();
        $this->assertArrayHasKey(CSRFToken::RP,$this->app->session->all());
        $this->assertNotNull($this->app->session->get(CSRFToken::RP));
    }

    public function testTokenConfirmRegister(){
        $this->userExtension->tokenConfirmRegister();
        $this->assertArrayHasKey(CSRFToken::CR,$this->app->session->all());
        $this->assertNotNull($this->app->session->get(CSRFToken::CR));
    }

    public function testShowDateNotif(){
        /** @var \DateTime $dateNotif */
        $dateNotif = new \DateTime();
        $this->assertContains('Seconds ago',$this->userExtension->showDateNotif($dateNotif));
        $dateNotif = $dateNotif->modify('-2 minute');
        $this->assertContains('Minutes ago',$this->userExtension->showDateNotif($dateNotif));
        $dateNotif = $dateNotif->modify('-10 minute');
        $this->assertContains('12 minutes ago',$this->userExtension->showDateNotif($dateNotif));
        $dateNotif = $dateNotif->modify('-1 hour');
        $this->assertContains('1 hour ago',$this->userExtension->showDateNotif($dateNotif));
        $dateNotif = $dateNotif->modify('-2 hour');
        $this->assertContains('3 hours ago',$this->userExtension->showDateNotif($dateNotif));
        $dateNotif = $dateNotif->modify('-2 day');
        $this->assertEquals($dateNotif->format('H:i Y-m-d'),$this->userExtension->showDateNotif($dateNotif));
    }


}

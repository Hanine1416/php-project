<?php

use MBComponents\Helpers\TokenGenerator;
use UserBundle\Entity\Banner;

class BannerTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    /**
     * @var $banner Banner
     */
    protected $banner;

    protected function _before()
    {
        $this->banner = new Banner();
    }

    protected function _after()
    {
    }
    /** Test token generator getter and setter faq */
    public function testTokenGeneratorBanner()
    {
        $token = $this->banner->getToken();
        $this->assertNotNull($token);
        $newToken = TokenGenerator::generateToken();
        $this->banner->setToken($newToken);
        $this->assertEquals($newToken, $this->banner->getToken());
    }
    /** Test banner title EN getter and setter */
    public function testTitleEN()
    {
        $titleEn = 'This is the English title';
        $this->banner->setTitleEN($titleEn);
        $this->assertEquals($titleEn, $this->banner->getTitleEN());
    }
    /** Test banner title ES getter and setter */
    public function testTitleES()
    {
        $titleEs = 'Este es el título español';
        $this->banner->setTitleES($titleEs);
        $this->assertEquals($titleEs, $this->banner->getTitleES());
    }
    /** Test banner title FR getter and setter */
    public function testTitleFR()
    {
        $titleFr = "C'est le titre français";
        $this->banner->setTitleFR($titleFr);
        $this->assertEquals($titleFr, $this->banner->getTitleFR());
    }
    /** Test banner title DE getter and setter */
    public function testTitleDE()
    {
        $titleDe = 'Dies ist der deutsche Titel';
        $this->banner->setTitleDE($titleDe);
        $this->assertEquals($titleDe, $this->banner->getTitleDE());
    }
    /** Test banner title ANZ getter and setter */
    public function testTitleANZ()
    {
        $titleANZ = 'titre ANZ';
        $this->banner->setTitleANZ($titleANZ);
        $this->assertEquals($titleANZ, $this->banner->getTitleANZ());
    }
    /** Test banner content EN getter and setter */
    public function testContentEN()
    {
        $contentEn = 'This is the English content';
        $this->banner->setContentEN($contentEn);
        $this->assertEquals($contentEn, $this->banner->getContentEN());
    }
    /** Test banner content ES getter and setter */
    public function testContentES()
    {
        $contentEs = 'Este es el título español';
        $this->banner->setContentES($contentEs);
        $this->assertEquals($contentEs, $this->banner->getContentES());
    }
    /** Test banner content FR getter and setter */
    public function testContentFR()
    {
        $contentFr = "C'est le contenu français";
        $this->banner->setContentFR($contentFr);
        $this->assertEquals($contentFr, $this->banner->getContentFR());
    }
    /** Test banner content DE getter and setter */
    public function testContentDE()
    {
        $contentDe = 'Dies ist der deutsche Titel';
        $this->banner->setContentDE($contentDe);
        $this->assertEquals($contentDe, $this->banner->getContentDE());
    }
    /** Test banner content ANZ getter and setter */
    public function testContentANZ()
    {
        $contentANZ = 'ANZ Content';
        $this->banner->setContentANZ($contentANZ);
        $this->assertEquals($contentANZ, $this->banner->getContentANZ());
    }
    /** Test banner type getter and setter */
    public function testType()
    {
        $orangeBanner = 'banner-orange';
        $blueBanner= 'banner-blue';
        $this->banner->setType($blueBanner);
        $this->assertNotEquals($orangeBanner, $this->banner->getType());
    }
    /** Test banner is closed getter and setter */
    public function testIsClosed()
    {
        $this->banner->setIsClosed(true);
        $this->assertEquals(true, $this->banner->getIsClosed());
        $this->assertNotEquals(false, $this->banner->getIsClosed());
    }
    /** Test banner is enabled getter and setter */
    public function testIsEnabled()
    {
        $this->banner->setIsEnabled(false);
        $this->assertEquals(false, $this->banner->getIsEnabled());
        $this->assertNotEquals(true, $this->banner->getIsEnabled());
    }
}

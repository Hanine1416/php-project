<?php

use MBComponents\Helpers\TokenGenerator;
use UserBundle\Entity\Faq;

class FaqTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    /**
     * @var $faq Faq
     */
    protected $faq;
    
    protected function _before()
    {
        $this->faq = new Faq();
    }

    protected function _after()
    {
    }
    /** Test token generator getter and setter faq */
    public function testTokenGeneratorFAQ()
    {
        $token = $this->faq->getToken();
        $this->assertNotNull($token);
        $newToken = TokenGenerator::generateToken();
        $this->faq->setToken($newToken);
        $this->assertEquals($newToken, $this->faq->getToken());
    }
    /** Test faq question getter and setter */
    public function testQuestionFaq()
    {
        $newQuestion = 'Is that a new question?';
        $this->faq->setQuestion($newQuestion);
        $this->assertEquals($newQuestion, $this->faq->getQuestion());
    }
    /** Test faq answer getter and setter */
    public function testAnswerFaq()
    {
        $answer = 'This is the answer';
        $this->faq->setAnswer($answer);
        $this->assertEquals($answer, $this->faq->getAnswer());
    }
    /** Test language getter and setter */
    public function testLanguageFaq()
    {
        $lang = 'fr';
        $this->faq->setLanguage($lang);
        $this->assertEquals($lang, $this->faq->getLanguage());
    }
    /** Test order getter and setter */
    public function testOrderFaq()
    {
        $order = 2;
        $this->faq->setOrder($order);
        $this->assertNotNull(3, $this->faq->getOrder());
        $this->assertNotNull(2, $this->faq->getOrder());
    }
}

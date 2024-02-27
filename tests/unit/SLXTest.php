<?php

use MBComponents\Test\AppTester;

class SLXTest extends AppTester
{

    public function testSLX(){
        $SLX = new \MBComponents\Services\SLX($this->app->sfContainer);
        $userService = $SLX->getUserService();
        $bookService = $SLX->getBookService();
        $this->assertNotNull($bookService);
        $this->assertNotNull($userService);
    }

}

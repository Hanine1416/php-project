<?php
/**
 * This file is part of the IC.
 * Created by mobelite.
 * Date: 5/24/18
 * Time: 13:20
 * @author: Mobelite <www.mobelite.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MBComponents\Services;

use MainBundle\Services\BookService;
use MainBundle\Services\ReadingListService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use UserBundle\Services\UserService;

/**
 * Class SLX
 * @package MBComponents\Services
 */
class SLX
{
    /**
     * @var SlxWebService
     */
    private $slxWebService;
    /**
     * @var UserService
     */
    private $userService;
    /**
     * @var BookService
     */
    private $bookService;
    /**
     * @var ReadingListService
     */
    private $readingListService;
    /**
     * SLX constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->slxWebService = new SlxWebService($container);
        $this->bookService = new BookService($container);
        $this->userService = new UserService($container);
        $this->readingListService = new ReadingListService($container);
    }

    /**
     * @return SlxWebService
     */
    public function getSlxWebService():SlxWebService
    {
        return $this->slxWebService;
    }

    /**
     * @return UserService
     */
    public function getUserService():UserService
    {
        return $this->userService;
    }

    /**
     * @return BookService
     */
    public function getBookService():BookService
    {
        return $this->bookService;
    }

    /**
     * @return ReadingListService
     */
    public function getReadingListService():ReadingListService
    {
        return $this->readingListService;
    }
}

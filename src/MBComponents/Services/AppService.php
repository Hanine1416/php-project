<?php
/**
 * Created by PhpStorm.
 * User: mobelite
 * Date: 25/05/2018
 * Time: 10:53
 */

namespace MBComponents\Services;

use MBComponents\Slim;
use Doctrine\ORM\EntityManager;
use UserBundle\Entity\Code;
use UserBundle\Entity\User;
use UserBundle\Services\UserService;

/**
 * Class AppService
 * @property  EntityManager
 * @package MBComponents\Services
 */
class AppService
{

    /** @var Slim $slim */
    private $slim;

    /**
     * AppService constructor.
     * @param Slim $slim
     */
    public function __construct(Slim $slim)
    {
        $this->slim = $slim;
    }

    /**
     * generate url for a route name
     * @param string $name
     * @param array $parameters
     * @return string
     * @throws \Exception
     */
    public function generateUrl(string $name, array $parameters = []): string
    {
        return $this->slim->getUrlGenerator()->generate($name, $parameters);
    }

    /**
     * add flash messages to the session
     * @param $type
     * @param $message
     */
    public function addFlash(string $type, string $message): void
    {
        try {
            $this->slim->flashes->addFlash($type, $message);
        } catch (\Exception $exception) {
            echo $exception->getMessage();
        }
    }


    /**
     * check if the user is logged in or not by looking at the session data
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        $session = $this->slim->session;
        return $session->has('userInfo');
    }

    /**
     * @return \UserBundle\Entity\User
     * @throws \Exception
     */
    public function getUser(): ?User
    {
        /** @var UserService $userService */
        $userService = $this->slim->getService(SLX::class)->getUserService();
        return $this->isLoggedIn() ? $userService->getUser() : null;
    }

    /**
     * Get address from brazil cep
     * @param $cep
     * @return mixed
     */
    public function getAddressFromCep($cep)
    {
        $opts = array('https' =>
            array(
                'method' => 'GET',
                'header' => 'Content-type: application/json',
            )
        );
        $context = stream_context_create($opts);
        $result = new \stdClass();
        try {
            $response = file_get_contents('http://api.postmon.com.br/v1/cep/' . $cep, false, $context);
            if ($response) {
                $result->data = json_decode($response);
                $result->success = true;
            } else {
                $result->success = false;
                $result->message = 'Invalid cep';
            }
        } catch (\Exception $e) {
            $result->success = false;
            $result->message = 'Invalid cep';
        }
        return $result;
    }
}

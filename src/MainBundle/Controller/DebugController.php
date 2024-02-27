<?php

/*
 * This file is part of the Inspection Copy.
 * Copyright (C) 2019 Elsevier.
 * Created by mobelite.
 *
 * Date: 4/11/18
 * Time: 17:38
 * @author: Mobelite <www.mobelite.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace MainBundle\Controller;

use lib\Config;
use MainBundle\Entity\WSDebug;
use MBComponents\Controller\Controller;

/**
 * Class DebugController
 * @package MainBundle\Controller
 */
class DebugController extends Controller
{
    /**
     * show all saleLogix Webservices status (by returning a response)
     * @throws \Exception
     */
    public function webServices()
    {
        $webServices = $this->getEntityManager()->getRepository(WSDebug::class)->findAll();
        return $this->render('@MainBundle/debug/web-services.html.twig', ['webServices' => $webServices]);
    }

    /**
     * show email destination for current web environment
     * @throws \Exception
     */
    public function emails()
    {
        $environment = Config::read('environment');
        $projectEmail = Config::read('projectEmail');
        $customerServiceEmail = Config::read('customerServiceEmail')[$this->language];
        $supportEmail = $this->trans('contact_us.support_email');
        return $this->render('@MainBundle/debug/emails.html.twig', [
            'environment' => $environment,
            'projectEmail' => $projectEmail,
            'customerServiceEmail' => $customerServiceEmail,
            'supportEmail' => $supportEmail
        ]);
    }
}

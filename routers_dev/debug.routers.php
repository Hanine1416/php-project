<?php
/**
 * This file is part of the IC.
 * User: Mobelite
 * Date: 5/30/18
 * Time: 09:13
 * @author: Mobelite <www.mobelite.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
$app->group('/:reg/:lang/debug', function () use ($app) {
    $app->get('/list-web-services', function () use ($app) {
        echo (new \MainBundle\Controller\DebugController())->webServices();
    })->setName('web-services');

    $app->get('/list-emails', function () use ($app) {
        echo (new \MainBundle\Controller\DebugController())->emails();
    })->setName('web-emails');
});

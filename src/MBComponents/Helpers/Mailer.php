<?php
/**
 * This file is part of the Inspection Copy.
 * Created by mobelite.
 * Date: 3/27/18
 * Time: 12:45
 * @author: Mobelite <www.mobelite.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MBComponents\Helpers;

use lib\Config;
use MBComponents\Monolog\Monolog;
use MBComponents\Slim;

/**
 * this class allow us to send mail
 * Class Mailer
 * @package MBComponents\Helpers
 */
class Mailer
{
    /**
     * Send email
     * @param $data
     * @param bool $redirect
     * @return array
     */
    public function sendEmail($data, $redirect = false)
    {
        $m = new \SimpleEmailServiceMessage();

        if (!isset($data['emailText'])) {
            $data['emailText'] = '';
        }

        /** redirect emails to project email for locals & develop environment */
        if (Config::has('environment') &&
            in_array(strtolower(Config::read('environment')), ['develop', 'local']) || $redirect) {
            $data['subject'] = $data['subject'] . '   => mailer intended for :';
            if (is_array($data['email'])) {
                $data['subject'] .= implode(',', $data['email']);
            } else {
                $data['subject'] .= $data['email'];
            }
            if (array_key_exists('cc', $data)){
                $data['subject'] = $data['subject'] . ', cc :'.$data['cc'];
            }
            $data['email'] = Config::read('projectEmail');
        }

        $m->addTo($data['email'])
            ->setFrom(Config::read('fromEmail'))
            ->setSubject($data['subject'])
            ->setMessageFromString($data['emailText'], $data['emailContent']);

        if (array_key_exists('cc', $data) && !$redirect) {
            $m->addCC($data['cc']);
        }

        /** get region endpoint */
        switch (Config::read('amazonRegion')) {
            case 'AWS_EU_WEST1':
                $regionEndpoint = \SimpleEmailService::AWS_EU_WEST1;
                break;
            case 'AWS_US_WEST_2':
                $regionEndpoint = \SimpleEmailService::AWS_US_WEST_2;
                break;
            case 'AWS_US_EAST_1':
                $regionEndpoint = \SimpleEmailService::AWS_US_EAST_1;
                break;
            default: $regionEndpoint = \SimpleEmailService::AWS_EU_WEST1;
        }
        $ses = new \SimpleEmailService(Config::read('amazonKey'), Config::read('amazonSecret'), $regionEndpoint);

        if (Config::read('debug')) {
            /** @var Slim $app */
            $app = Config::read('app');
            /** @var Monolog $logger */
            $logger = $app->getMonoLog();
            /** write to log */
            $logger->log('info', '_dev', "Try send email : ", [
                'recipient' => is_array($data['email']) ? implode(',', $data['email']) : $data['email'],
                'subject' => $data['subject'],
            ]);
        }

        return $ses->sendEmail($m);
    }
}

<?php
/**
 * This file is part of the Inspection Copy.
 * Created by mobelite.
 * Date: 3/28/18
 * Time: 15:16
 * @author: Mobelite <www.mobelite.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MBComponents\Twig;

use MBComponents\Helpers\Yaml\YamlManipulator;

/**
 * Class SlimTwig
 * @package MBComponents\Twig
 */
class SlimTwig extends \Slim\Views\Twig
{
    /**
     * @param string $template
     * @param null $data
     * @return string
     */
    public function render($template, $data = null):string
    {
        /** add configuration parameters as global vars */
        $data['configParameters'] = YamlManipulator::getFileContents(__DIR__ . '/../config/params.yml')['parameters'];

        return parent::render($template, $data);
    }
}

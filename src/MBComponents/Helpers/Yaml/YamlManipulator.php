<?php
/**
 * This file is part of the Inspection-Copy.
 * Created by Mobelite
 * Date: 5/16/17
 * Time: 09:22
 * @author: Mobelite <http://www.mobelite.fr/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MBComponents\Helpers\Yaml;

use Symfony\Component\Yaml\Yaml;

/**
 * class YamlManipulator
 * @package Components\Helper
 */
final class YamlManipulator implements YamlManipulatorInterface
{
    /**
     * @param $filename
     * @param string $key
     * @return array|mixed
     */
    public static function getParameters($filename, $key = 'parameters')
    {
        $parameters = [];
        if (file_exists($filename)) {
            $parameters = Yaml::parse(file_get_contents($filename));
            if (!is_array($parameters) || !isset($parameters[$key])) {
                $parameters[$key] = [];
            }
        }

        return $parameters;
    }

    /**
     * @param $filename
     * @return mixed|null
     */
    public static function getFileContents($filename)
    {
        if (file_exists($filename)) {
            return Yaml::parse(file_get_contents($filename));
        }

        return null;
    }

    /**
     * @param $filename
     * @param array $parameters
     */
    public static function updateParameters($filename, array $parameters = array())
    {
        if (file_exists($filename)) {
            if (!is_writable($filename)) {
                chmod($filename, 0777);
            }
            file_put_contents($filename, Yaml::dump($parameters, 10));
        }
    }
}

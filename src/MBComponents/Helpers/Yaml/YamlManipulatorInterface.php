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

/**
 * Interface YamlManipulatorInterface
 * @package Components\Helper
 */
interface YamlManipulatorInterface
{
    /**
     * @param $filename
     * @param string $key
     * @return mixed
     */
    public static function getParameters($filename, $key = 'parameters');

    /**
     * @param $filename
     * @return mixed
     */
    public static function getFileContents($filename);

    /**
     * @param $filename
     * @param array $parameters
     * @return mixed
     */
    public static function updateParameters($filename, array $parameters = array());
}

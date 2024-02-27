<?php
/**
 * This file is part of the IC.
 * Created by mobelite.
 * Date: 5/24/18
 * Time: 13:22
 * @author: Mobelite <www.mobelite.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MBComponents\Services;

/**
 * Interface SOAPInterface
 * @package MBComponents\Services
 */
interface SOAPInterface
{
    /**
     * @param $func
     * @param $params
     * @param bool $cache
     * @return mixed
     */
    public function getSoap($func, $params, $cache = false);
}

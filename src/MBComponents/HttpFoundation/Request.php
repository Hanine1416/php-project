<?php
/**
 * This file is part of the Inspection Copy.
 * Created by mobelite.
 * Date: 4/24/18
 * Time: 12:01
 * @author: Mobelite <www.mobelite.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MBComponents\HttpFoundation;

use lib\Config;

/**
 * Class Request
 * @package MBComponents\HttpFoundation
 */
class Request extends \Symfony\Component\HttpFoundation\Request
{
    /**
     * @var string
     */
    private $path;
    /**
     * @inheritdoc
     */
    public static function createFromGlobals()
    {
        $request = parent::createFromGlobals();
        $request->getPathInfo();

        $request->path = $request->requestUri;

        return $request;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}

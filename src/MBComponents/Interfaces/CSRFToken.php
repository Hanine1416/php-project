<?php
/**
 * This file is part of the Inspection Copy.
 * Created by mobelite.
 * Date: 3/29/18
 * Time: 16:52
 * @author: Mobelite <www.mobelite.fr>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MBComponents\Interfaces;

/**
 * Interface CSRFToken
 * @package MBComponents\Interfaces
 */
interface CSRFToken
{
    /**
     * reset password
     */
    const RP = '_token_reset_password';
    /**
     * confirm register
     */
    const CR = '_token_confirm_register';
}

<?php
/**
 * 2007-2021 Ingenico
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@ingenico.com we can send you a copy immediately.
 *
 * @author    Ingenico <contact@ingenico.com>
 * @copyright Ingenico
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use PHPUnit\Framework\TestCase;

class ingenico_epaymentsCronTest extends TestCase
{
    public function testAccess()
    {
        $module = Module::getInstanceByName('ingenico_epayments');
        $cron_url = $module->getControllerUrl('cron');
        $ch = curl_init($cron_url . '?token=wrongtoken');
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $this->assertEquals(403, $httpcode);
    }
}

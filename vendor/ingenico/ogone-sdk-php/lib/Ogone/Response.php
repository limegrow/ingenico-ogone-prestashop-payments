<?php
/*
 * This file is part of the Marlon Ogone package.
 *
 * (c) Marlon BVBA <info@marlon.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ogone;

interface Response
{

    /** @var string */
    const SHASIGN_FIELD = 'SHASIGN';

    /**
     * @deprecated
     */
    public function isSuccessful();

    /**
     * Check if response parameter exists
     * @param $key
     * @return bool
     */
    public function hasParam($key);

    /**
     * Retrieves a response parameter
     * @param string $key
     * @throws \InvalidArgumentException
     * @return string
     */
    public function getParam($key);
}

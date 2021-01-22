<?php

/*
 * This file is part of the Marlon Ogone package.
 *
 * (c) Marlon BVBA <info@marlon.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ogone\ShaComposer;

use Ogone\Encoding;
use Ogone\ParameterFilter\GeneralParameterFilter;
use Ogone\Passphrase;
use Ogone\HashAlgorithm;
use Ogone\ParameterFilter\ParameterFilter;

/**
 * SHA string composition the "new way", using all parameters in the ogone response
 */
class AllParametersShaComposer implements ShaComposer
{
    /** @var array of ParameterFilter */
    private $parameterFilters;

    /**
     * @var string Passphrase
     */
    private $passphrase;

    /**
     * @var HashAlgorithm
     */
    private $hashAlgorithm;

    public function __construct(Passphrase $passphrase, HashAlgorithm $hashAlgorithm = null)
    {
        $this->passphrase = $passphrase;

        $this->addParameterFilter(new GeneralParameterFilter);

        $this->hashAlgorithm = $hashAlgorithm ?: new HashAlgorithm(HashAlgorithm::HASH_SHA1);
    }

    /**
     * Compose SHA string based on Ingenico response parameters.
     *
     * @param array $parameters
     * @param bool $useLatinCharset Deprecated
     */
    public function compose(array $parameters, $useLatinCharset = false)
    {
        foreach ($this->parameterFilters as $parameterFilter) {
            $parameters = $parameterFilter->filter($parameters);
        }

        // Sort parameters using Collator
        if (extension_loaded('intl') === true) {
            $keys = array_keys($parameters);
            $values = array_values($parameters);
            collator_asort(collator_create('root'), $keys);

            $parameters = [];
            foreach ($keys as $index => $key) {
                $parameters[$key] = $values[$index];
            }
        } else {
            // This function have problem with SCO_CATEGORY and SCORING order
            ksort($parameters);
        }

        // compose SHA string
        $shaString = '';
        foreach ($parameters as $key => $value) {
            $shaString .= $key . '=' . $value . $this->passphrase;
        }

        return strtoupper(hash($this->hashAlgorithm, $shaString));
    }

    public function addParameterFilter(ParameterFilter $parameterFilter)
    {
        $this->parameterFilters[] = $parameterFilter;
    }
}

<?php
/*
 * This file is part of the Marlon Ogone package.
 *
 * (c) Marlon BVBA <info@marlon.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ogone\DirectLink;

use Ogone\AbstractPaymentResponse;
use SimpleXMLElement;
use InvalidArgumentException;

class DirectLinkPaymentResponse extends AbstractPaymentResponse
{

    public function __construct($xml_string)
    {
        libxml_use_internal_errors(true);

        if (simplexml_load_string($xml_string)) {
            $xmlResponse = new SimpleXMLElement($xml_string);

            $attributesArray = $this->xmlAttributesToArray($xmlResponse->attributes());

            // Check HTML_ANSWER if exists
            $answer = $xmlResponse->xpath('//HTML_ANSWER');
            if (count($answer) > 0) {
                $attributesArray['HTML_ANSWER'] = $answer[0]->__toString();
            }

            // use lowercase internally
            $attributesArray = array_change_key_case($attributesArray, CASE_UPPER);

            // filter request for Ogone parameters
            $this->parameters = $this->filterRequestParameters($attributesArray);

            if ($this->logger) {
                $this->logger->debug(sprintf('Response %s', get_class($this)), $this->parameters);
            }

        } else {
            throw new InvalidArgumentException("No valid XML-string given");
        }
    }

    private function xmlAttributesToArray($attributes)
    {
        $attributesArray = array();

        if (count($attributes)) {
            foreach ($attributes as $key => $value) {
                $attributesArray[(string)$key] = (string)$value;
            }
        }

        return $attributesArray;
    }
}

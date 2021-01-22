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

use InvalidArgumentException;
use Psr\Log\LoggerInterface;

abstract class AbstractResponse implements Response, \ArrayAccess
{
    /**
     * Available Ogone parameters.
     *
     * @var array
     */
    protected $ogoneFields = array(
        'AAVADDRESS',
        'AAVCHECK',
        'AAVMAIL',
        'AAVNAME',
        'AAVPHONE',
        'AAVZIP',
        'ACCEPTANCE',
        'ALIAS',
        'AMOUNT',
        'BIC',
        'BIN',
        'BRAND',
        'CARDNO',
        'CCCTY',
        'CN',
        'COLLECTOR_BIC',
        'COLLECTOR_IBAN',
        'COMPLUS',
        'CREATION_STATUS',
        'CREDITDEBIT',
        'CURRENCY',
        'CVCCHECK',
        'DCC_COMMPERCENTAGE',
        'DCC_CONVAMOUNT',
        'DCC_CONVCCY',
        'DCC_EXCHRATE',
        'DCC_EXCHRATESOURCE',
        'DCC_EXCHRATETS',
        'DCC_INDICATOR',
        'DCC_MARGINPERCENTAGE',
        'DCC_VALIDHOURS',
        'DEVICEID',
        'DIGESTCARDNO',
        'ECI',
        'ED',
        'EMAIL',
        'ENCCARDNO',
        'FXAMOUNT',
        'FXCURRENCY',
        'HTML_ANSWER',
        'IP',
        'IPCTY',
        'MANDATEID',
        'MOBILEMODE',
        'NBREMAILUSAGE',
        'NBRIPUSAGE',
        'NBRIPUSAGE_ALLTX',
        'NBRUSAGE',
        'NCSTATUS',
        'NCERROR',
        'NCERRORPLUS',
        'ORDERID',
        'PAYID',
        'PAYIDSUB',
        'PAYMENT_REFERENCE',
        'PM',
        'SCO_CATEGORY',
        'SCORING',
        'SEQUENCETYPE',
        'SIGNDATE',
        'STATUS',
        'SUBBRAND',
        'SUBSCRIPTION_ID',
        'TICKET',
        'TRXDATE',
        'VC',
    );

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var string
     */
    protected $shaSign;

    /** @var LoggerInterface|null */
    protected $logger;

    /**
     * @param array $httpRequest Typically $_REQUEST
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $httpRequest)
    {
        // use uppercase internally
        $httpRequest = array_change_key_case($httpRequest, CASE_UPPER);

        // set sha sign
        $this->shaSign = $this->extractShaSign($httpRequest);

        // filter request for Ogone parameters
        $this->parameters = $this->filterRequestParameters($httpRequest);

        if ($this->logger) {
            $this->logger->debug(sprintf('Response %s', get_class($this)), $this->parameters);
        }
    }

    /**
     * Sets Logger.
     *
     * @param LoggerInterface|null $logger
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Filter http request parameters.
     *
     * @param array $requestParameters
     *
     * @return array
     */
    protected function filterRequestParameters(array $requestParameters)
    {
        // filter request for Ogone parameters
        return array_intersect_key($requestParameters, array_flip($this->ogoneFields));
    }

    /**
     * Set Ogone SHA sign.
     *
     * @param array $parameters
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    protected function extractShaSign(array $parameters)
    {
        if (!array_key_exists(self::SHASIGN_FIELD, $parameters) || '' == $parameters[self::SHASIGN_FIELD]) {
            throw new InvalidArgumentException('SHASIGN parameter not present in parameters.');
        }

        return $parameters[self::SHASIGN_FIELD];
    }

    /**
     * Check if response parameter exists.
     *
     * @param $key
     *
     * @return bool
     */
    public function hasParam($key)
    {
        return array_key_exists($key, $this->parameters);
    }

    /**
     * Retrieves a response parameter.
     *
     * @param string $key
     *
     * @throws \InvalidArgumentException
     * @return string
     */
    public function getParam($key)
    {
        // always use uppercase
        $key = strtoupper($key);

        if (!$this->hasParam($key)) {
            throw new InvalidArgumentException('Parameter '.$key.' does not exist.');
        }

        return $this->parameters[$key];
    }

    /**
     * Set a Response parameter
     * @param $key
     * @param $value
     */
    public function setParam($key, $value)
    {
        $key = strtoupper($key);
        $this->parameters[$key] = $value;
    }

    /**
     * Get all parameters + SHASIGN
     * @return array
     */
    public function toArray()
    {
        return $this->parameters + array('SHASIGN' => $this->shaSign);
    }

    /**
     * Implementation of \ArrayAccess::offsetSet()
     *
     * @param string $offset
     * @param mixed $value
     * @return void
     * @link http://www.php.net/manual/en/arrayaccess.offsetset.php
     */
    public function offsetSet($offset, $value)
    {
        $offset = strtoupper($offset);
        if (is_null($offset)) {
            $this->parameters[] = $value;
        } else {
            $this->parameters[$offset] = $value;
        }
    }

    /**
     * Implementation of \ArrayAccess::offsetExists()
     *
     * @param string $offset
     * @return bool
     * @link http://www.php.net/manual/en/arrayaccess.offsetexists.php
     */
    public function offsetExists($offset)
    {
        $offset = strtoupper($offset);
        return isset($this->parameters[$offset]);
    }

    /**
     * Implementation of \ArrayAccess::offsetUnset()
     *
     * @param string $offset
     * @return void
     * @link http://www.php.net/manual/en/arrayaccess.offsetunset.php
     */
    public function offsetUnset($offset)
    {
        $offset = strtoupper($offset);
        unset($this->parameters[$offset]);
    }

    /**
     * Implementation of \ArrayAccess::offsetGet()
     *
     * @param string $offset
     * @return mixed
     * @link http://www.php.net/manual/en/arrayaccess.offsetget.php
     */
    public function offsetGet($offset)
    {
        $offset = strtoupper($offset);
        return isset($this->parameters[$offset]) ? $this->parameters[$offset] : null;
    }

    /**
     * Set/Get attribute wrapper
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed
     * @throws \Exception
     */
    public function __call($method, $arguments)
    {
        $key = strtoupper(substr($method, 3, strlen($method)));
        switch (substr($method, 0, 3)) {
            case 'get':
                return isset($this->parameters[$key]) ? $this->parameters[$key] : null;
            case 'set':
                $this->parameters[$key] = isset($arguments[0]) ? $arguments[0] : null;
                return $this;
            case 'uns':
                unset($this->parameters[$key]);
                return $this;
            case 'has':
                return isset($this->parameters[$key]);
        }

        throw new \Exception(sprintf('Invalid method %s::%s', get_class($this), $method));
    }
}

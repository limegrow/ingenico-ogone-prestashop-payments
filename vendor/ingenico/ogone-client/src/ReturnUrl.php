<?php

namespace IngenicoClient;

/**
 * Class ReturnUrl
 * @method $this setAcceptUrl($value)
 * @method string getAcceptUrl()
 * @method $this setDeclineUrl($value)
 * @method string getDeclineUrl()
 * @method $this setExceptionUrl($value)
 * @method string getExceptionUrl()
 * @method $this setCancelUrl($value)
 * @method string getCancelUrl()
 * @method $this setBackUrl($value)
 * @method string getBackUrl()
 */
class ReturnUrl extends Data
{
    const ACCEPT_URL = 'accept_url';
    const DECLINE_URL = 'decline_url';
    const EXCEPTION_URL = 'exception_url';
    const CANCEL_URL = 'cancel_url';
    const BACK_URL = 'back_url';

    /**
     * ReturnUrl constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->setData($data);
    }

    /**
     * Get Urls.
     *
     * @return string[]
     */
    public function getUrls()
    {
        return [
            'accept' => $this->getAcceptUrl(),
            'decline' => $this->getDeclineUrl(),
            'exception' => $this->getExceptionUrl(),
            'cancel' => $this->getCancelUrl(),
            'back' => $this->getBackUrl()
        ];
    }
}

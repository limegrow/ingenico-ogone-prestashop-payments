<?php

namespace IngenicoClient;

use Psr\Log\LoggerInterface;

/**
 * Class Client.
 */
class Client
{
    /** @var LoggerInterface|null */
    protected $logger;

    /**
     * Request constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function post(array $params, $url, $shaSign)
    {
        $body = [];

        foreach ($params as $key => $value) {
            // Convert to text field
            if (is_object($value) && method_exists($value, '__toString')) {
                $value = $value->__toString();
            }

            $body[strtoupper($key)] = $value;
        }

        $body['SHASIGN'] = $shaSign;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt(
            $ch,
            CURLOPT_POSTFIELDS,
            http_build_query($body)
        );
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $info = curl_getinfo($ch);

        $error = null;
        $errno = null;
        if (!$info['http_code']) {
            $error = curl_error($ch);
            $errno = curl_errno($ch);
        }

        curl_close($ch);

        if ($this->logger) {
            $this->logger->debug(sprintf('Post request to: %s', $url), [
                'url' => $url,
                'shasign' => $shaSign,
                'params' => $body,
                'response' => $response,
                'http_code' => $info['http_code'],
                'error' => $error,
                'errno' => $errno
            ]);
        }

        return $response;
    }
}

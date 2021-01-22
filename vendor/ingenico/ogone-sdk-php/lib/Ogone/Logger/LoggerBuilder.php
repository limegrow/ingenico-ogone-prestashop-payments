<?php
/**
 * Created by PhpStorm.
 * User: alexw
 * Date: 21/01/19
 * Time: 21:11.
 */

namespace Ogone\Logger;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\WebProcessor;
use Psr\Log\LoggerInterface;

/**
 * Class LoggerBuilder.
 */
class LoggerBuilder
{
    /** @var LoggerInterface */
    protected $logger;

    /**
     * Gets Logger.
     *
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * build logger.
     *
     * @param $channel
     * @param string $path
     * @param int    $level
     *
     * @return LoggerBuilder
     *
     * @throws \Exception
     */
    public function createLogger($channel, $path = '/tmp/ogone_sdk.log', $level = Logger::DEBUG)
    {
        $this->logger = new Logger($channel);
        $this->logger->pushHandler(new StreamHandler($path, $level));
        $this->logger->pushProcessor(new WebProcessor());

        return $this;
    }
}

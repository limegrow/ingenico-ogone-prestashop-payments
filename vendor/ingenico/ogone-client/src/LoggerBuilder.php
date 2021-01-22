<?php
/**
 * Created by PhpStorm.
 * User: alexw
 * Date: 22/01/19
 * Time: 19:12.
 */

namespace IngenicoClient;

use Gelf\Publisher;
use Gelf\Transport\TcpTransport;
use Monolog\Formatter\GelfMessageFormatter;
use Monolog\Handler\GelfHandler;
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
     * @param string $channel
     * @param string $path
     * @param int    $level
     *
     * @return LoggerBuilder
     *
     * @throws \Exception
     */
    public function createLogger($channel, $path = '/tmp/ingenico_sdk.log', $level = Logger::DEBUG)
    {
        $this->logger = new Logger($channel);
        $this->logger->pushHandler(new StreamHandler($path, $level));
        $this->logger->pushProcessor(new WebProcessor());

        return $this;
    }

    /**
     * build Gelf logger
     *
     * @param string $channel
     * @param string $host
     * @param int $port
     * @param int $level
     *
     * @return $this
     */
    public function createGelfLogger($channel, $host, $port = 12201, $level = Logger::DEBUG)
    {
        $transport = new TcpTransport($host, $port);
        $publisher = new Publisher($transport);

        $handler = new GelfHandler($publisher, $level);
        $handler->setFormatter(new GelfMessageFormatter());

        $this->logger = new Logger($channel);
        $this->logger->pushHandler($handler);

        return $this;
    }
}

<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Service;

use Psr\Log\LoggerInterface;

class RuntimeParameterBagLogger
{
    private $level;
    private $logger;

    /**
     * Constructor.
     *
     * @param string          $level  Log level (should correspond to a logger method)
     * @param LoggerInterface $logger Logger service
     * @throws \InvalidArgumentException if level does not correspond to a method in LoggerInterface
     */
    public function __construct($level, LoggerInterface $logger = null)
    {
        $levels = array_filter(get_class_methods(LoggerInterface::class), function($method) {
            return $method !== 'log';
        });

        if (!in_array($level, $levels)) {
            throw new \InvalidArgumentException(sprintf('The "%s" level does not correspond to a method in LoggerInterface', $level));
        }

        $this->level = $level;
        $this->logger = $logger;
    }

    /**
     * Log a message at the specified level.
     *
     * @param string $message
     */
    public function log($message)
    {
        if (null !== $this->logger) {
            call_user_func(array($this->logger, $this->level), $message);
        }
    }
}

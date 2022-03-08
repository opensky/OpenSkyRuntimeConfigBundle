<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Service;

use OpenSky\Bundle\RuntimeConfigBundle\Util\LogUtil;
use Psr\Log\LoggerInterface;

class RuntimeParameterBagLogger
{
    /**
     * @var string
     */
    private $level;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param string $level Log level (should correspond to a logger method)
     * @param LoggerInterface|null $logger Logger service
     */
    public function __construct($level, LoggerInterface $logger = null)
    {
        if (!in_array($level, LogUtil::getValidLogLevels())) {
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
            call_user_func([$this->logger, $this->level], $message);
        }
    }
}

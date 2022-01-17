<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Service;

use OpenSky\Bundle\RuntimeConfigBundle\Util\LogUtil;
use Psr\Log\LoggerInterface;

class RuntimeParameterBagLogger
{
    private string $level;
    private LoggerInterface|null $logger;

    /**
     * Constructor.
     *
     * @param string                     $level  Log level (should correspond to a logger method)
     * @param LoggerInterface|null       $logger Logger service
     * @throws \InvalidArgumentException if level does not correspond to a method in LoggerInterface
     */
    public function __construct(string $level, LoggerInterface $logger = null)
    {
        if (!in_array($level, LogUtil::getValidLogLevels())) {
            throw new \InvalidArgumentException(sprintf(
                'The "%s" level does not correspond to a method in LoggerInterface',
                $level
            ));
        }

        $this->level = $level;
        $this->logger = $logger;
    }

    /**
     * Log a message at the specified level.
     *
     * @param string|\Stringable $message
     */
    public function log(string|\Stringable $message)
    {
        if (null !== $this->logger) {
            call_user_func([$this->logger, $this->level], $message);
        }
    }
}

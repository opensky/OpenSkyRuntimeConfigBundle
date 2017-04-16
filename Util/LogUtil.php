<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Util;

use Psr\Log\LoggerInterface;

final class LogUtil
{
    /**
     * @return array
     */
    public static function getValidLogLevels()
    {
        return array_filter(get_class_methods(LoggerInterface::class), function($method) {
            return $method !== 'log';
        });
    }
}

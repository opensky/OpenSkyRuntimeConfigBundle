<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Tests\Service;

use OpenSky\Bundle\RuntimeConfigBundle\Service\RuntimeParameterBagLogger;
use OpenSky\Bundle\RuntimeConfigBundle\Util\LogUtil;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RuntimeParameterBagLoggerTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorShouldThrowExceptionForInvalidLevel()
    {
        new RuntimeParameterBagLogger('foo');
    }

    /**
     * @dataProvider provideValidLogLevels
     */
    public function testShouldLogWithValidLevel($level)
    {
        $innerLogger = $this->createMock(LoggerInterface::class);

        $innerLogger->expects($this->once())
            ->method($level)
            ->with('message');

        $logger = new RuntimeParameterBagLogger($level, $innerLogger);
        $logger->log('message');
    }

    /**
     * @dataProvider provideValidLogLevels
     */
    public function testShouldDoNothingWithoutInnerLogger($level)
    {
        $logger = new RuntimeParameterBagLogger($level);
        $logger->log('message');
    }

    /**
     * @return array
     */
    public function provideValidLogLevels()
    {
        return array_map(function($level) {
            return [$level];
        }, LogUtil::getValidLogLevels());
    }
}

<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Tests\Service;

use OpenSky\Bundle\RuntimeConfigBundle\Service\RuntimeParameterBagLogger;
use OpenSky\Bundle\RuntimeConfigBundle\Util\LogUtil;
use Symfony\Component\HttpKernel\Log\LoggerInterface;

class RuntimeParameterBagLoggerTest extends \PHPUnit_Framework_TestCase
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
        $innerLogger = $this->getMock(LoggerInterface::class);

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

    public function provideValidLogLevels()
    {
        return array_map(
            function($level){ return (array) $level; },
            LogUtil::getValidLogLevels()
        );
    }
}

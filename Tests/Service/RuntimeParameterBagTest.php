<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Tests\Service;

use OpenSky\Bundle\RuntimeConfigBundle\Model\ParameterProviderInterface;
use OpenSky\Bundle\RuntimeConfigBundle\Service\RuntimeParameterBag;
use OpenSky\Bundle\RuntimeConfigBundle\Service\RuntimeParameterBagLogger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RuntimeParameterBagTest extends TestCase
{
    public function testShouldImplementContainerAwareInterface()
    {
        $bag = new RuntimeParameterBag($this->getMockParameterProvider());

        $this->assertInstanceOf(ContainerAwareInterface::class, $bag);
    }

    public function testAllShouldReturnAllParameters()
    {
        $parameters = array(
            'foo' => 'bar',
            'fuu' => 'baz',
        );

        $bag = new RuntimeParameterBag($this->getMockParameterProvider($parameters));

        $this->assertEquals($parameters, $bag->all());
    }

    public function testHasShouldReturnWhetherAParameterExists()
    {
        $parameters = array(
            'foo' => 'bar',
        );

        $bag = new RuntimeParameterBag($this->getMockParameterProvider($parameters));

        $this->assertTrue($bag->has('foo'));
        $this->assertFalse($bag->has('bar'));
    }

    public function testGetShouldReturnParameterValues()
    {
        $parameters = array(
            'foo' => 'bar',
            'fuu' => 'baz',
        );

        $bag = new RuntimeParameterBag($this->getMockParameterProvider($parameters));

        $this->assertEquals('bar', $bag->get('foo'));
        $this->assertEquals('baz', $bag->get('fuu'));
    }

    public function testDeinitialize()
    {
        $provider = $this->createMock(ParameterProviderInterface::class);

        $bag = new RuntimeParameterBag($provider);

        $parameters1 = array(
            'foo' => 'bar',
            'fuu' => 'baz',
        );

        $provider->expects($this->at(0))
            ->method('getParametersAsKeyValueHash')
            ->willReturn($parameters1);

        $parameters2 = array(
            'foo2' => 'bar2',
            'fuu2' => 'baz2',
        );

        $provider->expects($this->at(1))
            ->method('getParametersAsKeyValueHash')
            ->willReturn($parameters2);

        $this->assertEquals('bar', $bag->get('foo'));
        $this->assertEquals('baz', $bag->get('fuu'));

        $bag->deinitialize();

        $this->assertEquals('bar2', $bag->get('foo2'));
        $this->assertEquals('baz2', $bag->get('fuu2'));
    }

    public function testGetShouldDeferToContainerForUndefinedParameterWithContainer()
    {
        $container = $this->createMock(ContainerInterface::class);

        $container->expects($this->once())
            ->method('getParameter')
            ->with('foo')
            ->willReturn('bar');

        $bag = new RuntimeParameterBag($this->getMockParameterProvider());
        $bag->setContainer($container);

        $this->assertEquals('bar', $bag->get('foo'));
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException
     */
    public function testGetShouldThrowExceptionForUndefinedParameterWithoutContainer()
    {
        $bag = new RuntimeParameterBag($this->getMockParameterProvider());

        $bag->setContainer(new Container());

        $bag->get('foo');
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException
     */
    public function testGetShouldLogNonexistentParameterWithAvailableLogger()
    {
        $bag = new RuntimeParameterBag($this->getMockParameterProvider(), $this->getMockRuntimeParameterBagLogger('foo'));

        $bag->setContainer(new Container());

        $bag->get('foo');
    }

    private function getMockParameterProvider(array $parameters = array())
    {
        $provider = $this->createMock(ParameterProviderInterface::class);

        $provider->expects($this->any())
            ->method('getParametersAsKeyValueHash')
            ->willReturn($parameters);

        return $provider;
    }

    private function getMockRuntimeParameterBagLogger($expectedLogArgumentContains)
    {
        $logger = $this->getMockBuilder(RuntimeParameterBagLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger->expects($this->any())
            ->method('log')
            ->with($this->stringContains($expectedLogArgumentContains));

        return $logger;
    }
}

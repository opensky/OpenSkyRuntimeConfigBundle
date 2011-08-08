<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Tests\Service;

use OpenSky\Bundle\RuntimeConfigBundle\Service\RuntimeParameterBag;

class RuntimeParameterBagTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldImplementContainerAwareInterface()
    {
        $bag = new RuntimeParameterBag($this->getMockParameterProvider());

        $this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerAwareInterface', $bag);
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

    public function testGetShouldDeferToContainerForUndefinedParameterWithContainer()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $container->expects($this->once())
            ->method('getParameter')
            ->with('foo')
            ->will($this->returnValue('bar'));

        $bag = new RuntimeParameterBag($this->getMockParameterProvider());
        $bag->setContainer($container);

        $this->assertEquals('bar', $bag->get('foo'));
    }

    /**
     * @expectedException Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException
     */
    public function testGetShouldThrowExceptionForUndefinedParameterWithoutContainer()
    {
        $bag = new RuntimeParameterBag($this->getMockParameterProvider());

        $bag->get('foo');
    }

    /**
     * @expectedException Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException
     */
    public function testGetShouldLogNonexistentParameterWithAvailableLogger()
    {
        $bag = new RuntimeParameterBag($this->getMockParameterProvider(), $this->getMockRuntimeParameterBagLogger('foo'));

        $bag->get('foo');
    }

    private function getMockParameterProvider(array $parameters = array())
    {
        $provider = $this->getMock('OpenSky\Bundle\RuntimeConfigBundle\Model\ParameterProviderInterface');

        $provider->expects($this->any())
            ->method('getParametersAsKeyValueHash')
            ->will($this->returnValue($parameters));

        return $provider;
    }

    private function getMockRuntimeParameterBagLogger($expectedLogArgumentContains)
    {
        $logger = $this->getMockBuilder('OpenSky\Bundle\RuntimeConfigBundle\Service\RuntimeParameterBagLogger')
            ->disableOriginalConstructor()
            ->getMock();

        $logger->expects($this->once())
            ->method('log')
            ->with($this->stringContains($expectedLogArgumentContains));

        return $logger;
    }
}

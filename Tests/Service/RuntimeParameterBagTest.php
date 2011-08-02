<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Tests\Entity;

use OpenSky\Bundle\RuntimeConfigBundle\Service\RuntimeParameterBag;

class RuntimeParameterBagTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorDefaultsToStrictMode()
    {
        $bag = new RuntimeParameterBag($this->getMockParameterProvider(array()));

        $this->assertTrue($bag->isStrict());
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

    public function testGetShouldReturnNullForNonexistentParameterWhenNotStrict()
    {
        $bag = new RuntimeParameterBag($this->getMockParameterProvider(array()), false);

        $this->assertNull($bag->get('foo'));
    }

    /**
     * @expectedException Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException
     */
    public function testGetShouldThrowExceptionForNonexistentParameterWhenStrict()
    {
        $bag = new RuntimeParameterBag($this->getMockParameterProvider(array()));

        $bag->get('foo');
    }

    private function getMockParameterProvider(array $parameters)
    {
        $provider = $this->getMock('OpenSky\Bundle\RuntimeConfigBundle\Service\ParameterProviderInterface');

        $provider->expects($this->any())
            ->method('getParametersAsKeyValueHash')
            ->will($this->returnValue($parameters));

        return $provider;
    }
}

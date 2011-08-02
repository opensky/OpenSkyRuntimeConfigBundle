<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Tests\Entity;

use OpenSky\Bundle\RuntimeConfigBundle\Entity\Parameter;

class ParameterTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldSetAndGetKeyProperty()
    {
        $parameter = new Parameter();

        $parameter->setKey('foo');
        $this->assertEquals('foo', $parameter->getKey());
    }

    public function testShouldSetAndGetValueProperty()
    {
        $parameter = new Parameter();

        $parameter->setValue('foo');
        $this->assertEquals('foo', $parameter->getValue());
    }
}

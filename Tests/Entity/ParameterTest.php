<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Tests\Entity;

use OpenSky\Bundle\RuntimeConfigBundle\Entity\Parameter;

class ParameterTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldSetAndGetNameProperty()
    {
        $parameter = new Parameter();

        $parameter->setName('foo');
        $this->assertEquals('foo', $parameter->getName());
    }

    public function testShouldSetAndGetValueProperty()
    {
        $parameter = new Parameter();

        $parameter->setValue('foo');
        $this->assertEquals('foo', $parameter->getValue());
    }
}

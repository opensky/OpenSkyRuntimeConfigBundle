<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Tests\Model;

use OpenSky\Bundle\RuntimeConfigBundle\Model\Parameter;

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

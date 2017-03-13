<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Tests\Model;

use OpenSky\Bundle\RuntimeConfigBundle\Model\Parameter;
use PHPUnit\Framework\TestCase;

class ParameterTest extends TestCase
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

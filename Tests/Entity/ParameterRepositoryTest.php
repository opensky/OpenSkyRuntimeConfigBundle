<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Tests\Entity;

class ParameterRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!class_exists('Doctrine\ORM\EntityRepository')) {
            $this->markTestSkipped('Doctrine ORM library is not available');
        }
    }

    public function testShouldImplementParameterProviderInterface()
    {
        $repository = $this->getMockBuilder('OpenSky\Bundle\RuntimeConfigBundle\Entity\ParameterRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInstanceOf('OpenSky\Bundle\RuntimeConfigBundle\Model\ParameterProviderInterface', $repository);
    }

    /**
     * @dataProvider provideQueryResultAndExpectedParameters
     */
    public function testGetParametersAsKeyValueHashShouldExecuteQuery(array $queryResults, array $expectedParameters)
    {
        $repository = $this->getMockBuilder('OpenSky\Bundle\RuntimeConfigBundle\Entity\ParameterRepository')
            ->disableOriginalConstructor()
            ->setMethods(array('createQueryBuilder'))
            ->getMock();

        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('getResult', 'getSQL', '_doExecute'))
            ->getMock();

        $query->expects($this->once())
            ->method('getResult')
            ->will($this->returnValue($queryResults));

        $queryBuilder->expects($this->at(0))
            ->method('select')
            ->will($this->returnValue($queryBuilder));

        $queryBuilder->expects($this->at(1))
            ->method('getQuery')
            ->will($this->returnValue($query));

        $repository->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($queryBuilder));

        $this->assertEquals($expectedParameters, $repository->getParametersAsKeyValueHash());
    }

    public function provideQueryResultAndExpectedParameters()
    {
        return array(
            array(
                array(),
                array(),
            ),
            array(
                array(
                    array('name' => 'foo', 'value' => 'bar'),
                    array('name' => 'fuu', 'value' => 'baz'),
                ),
                array('foo' => 'bar', 'fuu' => 'baz'),
            )
        );
    }
}

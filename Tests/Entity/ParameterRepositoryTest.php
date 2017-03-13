<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Tests\Entity;

use OpenSky\Bundle\RuntimeConfigBundle\Entity\ParameterRepository;
use OpenSky\Bundle\RuntimeConfigBundle\Model\ParameterProviderInterface;
use PHPUnit\Framework\TestCase;

class ParameterRepositoryTest extends TestCase
{
    public function setUp()
    {
        if (!class_exists('Doctrine\ORM\EntityRepository')) {
            $this->markTestSkipped('Doctrine ORM library is not available');
        }
    }

    public function testShouldImplementParameterProviderInterface()
    {
        $repository = $this->getMockBuilder(ParameterRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInstanceOf(ParameterProviderInterface::class, $repository);
    }

    /**
     * @dataProvider provideQueryResultAndExpectedParameters
     */
    public function testGetParametersAsKeyValueHashShouldExecuteQuery(array $queryResults, array $expectedParameters)
    {
        $repository = $this->getMockBuilder(ParameterRepository::class)
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
            ->willReturn($queryResults);

        $queryBuilder->expects($this->at(0))
            ->method('select')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->at(1))
            ->method('getQuery')
            ->willReturn($query);

        $repository->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

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

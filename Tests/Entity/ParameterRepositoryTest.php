<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Tests\Entity;

use OpenSky\Bundle\RuntimeConfigBundle\Entity\ParameterRepository;
use OpenSky\Bundle\RuntimeConfigBundle\Model\ParameterProviderInterface;
use PHPUnit\Framework\TestCase;

class ParameterRepositoryTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        if (!class_exists('Doctrine\ORM\EntityRepository')) {
            $this->markTestSkipped('Doctrine ORM library is not available');
        }
    }

    public function testShouldImplementParameterProviderInterface()
    {
        $repository = $this->createMock(ParameterRepository::class);

        $this->assertInstanceOf(ParameterProviderInterface::class, $repository);
    }

    /**
     * @param array $queryResults
     * @param array $expectedParameters
     *
     * @dataProvider provideQueryResultAndExpectedParameters
     */
    public function testGetParametersAsKeyValueHashShouldExecuteQuery(array $queryResults, array $expectedParameters)
    {
        $repository = $this->getMockBuilder(ParameterRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();

        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->onlyMethods(['getResult', 'getSQL', '_doExecute'])
            ->getMock();

        $query->expects($this->once())
            ->method('getResult')
            ->willReturn($queryResults);

        $queryBuilder->expects($this->once())
            ->method('select')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $repository->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $this->assertEquals($expectedParameters, $repository->getParametersAsKeyValueHash());
    }

    public function provideQueryResultAndExpectedParameters()
    {
        return [
            [
                [],
                [],
            ],
            [
                [
                    ['name' => 'foo', 'value' => 'bar'],
                    ['name' => 'fuu', 'value' => 'baz'],
                ],
                ['foo' => 'bar', 'fuu' => 'baz'],
            ],
        ];
    }
}

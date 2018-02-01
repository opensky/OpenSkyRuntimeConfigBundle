<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Entity;

use Doctrine\ORM\EntityRepository;
use OpenSky\Bundle\RuntimeConfigBundle\Model\ParameterProviderInterface;

class ParameterRepository extends EntityRepository implements ParameterProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getParametersAsKeyValueHash()
    {
        $results = $this->createQueryBuilder('p')
            ->select('p.name', 'p.value')
            ->getQuery()
            ->getResult();

        $parameters = [];

        foreach ($results as $result) {
            $parameters[$result['name']] = $result['value'];
        }

        return $parameters;
    }
}

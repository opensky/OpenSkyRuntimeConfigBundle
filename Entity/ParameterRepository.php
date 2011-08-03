<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Entity;

use Doctrine\ORM\EntityRepository;
use OpenSky\Bundle\RuntimeConfigBundle\Service\ParameterProviderInterface;

class ParameterRepository extends EntityRepository implements ParameterProviderInterface
{
    /**
     * @see OpenSky\Bundle\RuntimeConfigBundle\ParameterProvider\ParameterProviderInterface::getParametersAsKeyValueHash()
     */
    public function getParametersAsKeyValueHash()
    {
        $results = $this->createQueryBuilder('p')
            ->select('p.name', 'p.value')
            ->getQuery()
            ->getResult();

        $parameters = array();

        foreach ($results as $result) {
            $parameters[$result['name']] = $result['value'];
        }

        return $parameters;
    }
}

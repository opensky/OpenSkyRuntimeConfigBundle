<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Model;

interface ParameterProviderInterface
{
    /**
     * Provide parameters for a RuntimeParameterBag as a key/value hash.
     *
     * @return array
     */
    function getParametersAsKeyValueHash(): array;
}

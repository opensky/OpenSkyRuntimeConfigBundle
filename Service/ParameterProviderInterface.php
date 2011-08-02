<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Service;

interface ParameterProviderInterface
{
    /**
     * Provide parameters for a RuntimeConfiguration as a key/value hash.
     *
     * @return array
     */
    function getParametersAsKeyValueHash();
}

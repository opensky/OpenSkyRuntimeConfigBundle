<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\DependencyInjection;

use OpenSky\Bundle\RuntimeConfigBundle\Service\RuntimeConfig;

/**
 * RuntimeConfigAwareInterface should be implemented by classes that depend on
 * a RuntimeConfig instance.
 */
interface RuntimeConfigAwareInterface
{
    function setRuntimeConfig(RuntimeConfig $runtimeConfig);
}

<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\DependencyInjection;

use OpenSky\Bundle\RuntimeConfigBundle\Service\RuntimeParameterBag;

/**
 * RuntimeParameterBagAwareInterface may be implemented by classes that depend
 * on a RuntimeParameterBag instance.
 *
 * Note: this is intended to support interface injection.
 */
interface RuntimeParameterBagAwareInterface
{
    function setRuntimeParameterBag(RuntimeParameterBag $runtimeParameterBag);
}

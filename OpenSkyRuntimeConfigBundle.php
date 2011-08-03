<?php

namespace OpenSky\Bundle\RuntimeConfigBundle;

use OpenSky\Bundle\RuntimeConfigBundle\DependencyInjection\OpenSkyRuntimeConfigExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OpenSkyRuntimeConfigBundle extends Bundle
{
    public function __construct()
    {
        $this->extension = new OpenSkyRuntimeConfigExtension();
    }
}

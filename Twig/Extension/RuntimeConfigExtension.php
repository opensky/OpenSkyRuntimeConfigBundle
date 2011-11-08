<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Twig\Extension;

use OpenSky\Bundle\RuntimeConfigBundle\Service\RuntimeParameterBag;

class RuntimeConfigExtension extends \Twig_Extension
{
    protected $runtimeConfig;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(RuntimeParameterBag $runtimeConfig)
    {
        $this->runtimeConfig = $runtimeConfig;
    }

    /**
     * Returns a list of global functions to add to the existing list.
     *
     * @return array An array of global functions
     */
    public function getFunctions()
    {
        return array(
            'runtime_config' => new \Twig_Function_Method($this, 'getRuntimeConfig'),
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'runtime_config';
    }

    public function getRuntimeConfig($name)
    {
        return $this->runtimeConfig->get($name);
    }
}

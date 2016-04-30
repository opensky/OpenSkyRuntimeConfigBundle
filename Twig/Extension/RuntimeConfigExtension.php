<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Twig\Extension;

use OpenSky\Bundle\RuntimeConfigBundle\Service\RuntimeParameterBag;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;

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
            new \Twig_SimpleFunction('runtime_config', array($this, 'getRuntimeConfig')),
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
        try {
            return $this->runtimeConfig->get($name);
        } catch (ParameterNotFoundException $e) {
            return null;
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }
}

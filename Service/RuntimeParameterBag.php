<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Service;

use OpenSky\Bundle\RuntimeConfigBundle\Model\ParameterProviderInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;

class RuntimeParameterBag extends FrozenParameterBag implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface|null
     */
    private $container;

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @var RuntimeParameterBagLogger|null
     */
    private $logger;

    /**
     * @var ParameterProviderInterface
     */
    private $parameterProvider;

    /**
     * Constructor.
     *
     * @param ParameterProviderInterface $parameterProvider Parameter provider
     * @param RuntimeParameterBagLogger $logger            Logger
     */
    public function __construct(ParameterProviderInterface $parameterProvider, RuntimeParameterBagLogger $logger = null)
    {
        parent::__construct();

        $this->parameterProvider = $parameterProvider;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Gets all defined parameters. This method does not consider parameters
     * from the service container, regardless of its availability.
     *
     * {@inheritDoc}
     */
    public function all()
    {
        $this->initialize();

        return parent::all();
    }

    /**
     * Gets a parameter by name. If the parameter is undefined, this method will
     * defer to the service container if available.
     *
     * {@inheritDoc}
     */
    public function get($name)
    {
        $this->initialize();

        if (!isset($this->parameters[$name]) && !array_key_exists($name, $this->parameters)) {
            if ($this->container) {
                return $this->container->getParameter($name);
            }

            return null;
        }

        return $this->parameters[$name];
    }

    /**
     * Returns whether a parameter is defined. This method does not consider
     * parameters from the service container, regardless of its availability.
     *
     * {@inheritDoc}
     */
    public function has($name)
    {
        $this->initialize();

        return parent::has($name);
    }

    public function deinitialize()
    {
        $this->parameters = [];
        $this->initialized = false;
    }

    private function initialize()
    {
        if ($this->initialized) {
            return;
        }

        $this->parameters = $this->parameterProvider->getParametersAsKeyValueHash();
        $this->initialized = true;
    }
}

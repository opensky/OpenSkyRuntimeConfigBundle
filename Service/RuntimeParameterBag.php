<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Service;

use OpenSky\Bundle\RuntimeConfigBundle\Model\ParameterProviderInterface;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;

class RuntimeParameterBag extends FrozenParameterBag
{
    private $initialized = false;
    private $logger;
    private $parameterProvider;
    private $strict;

    /**
     * Constructor.
     *
     * @param ParameterProvider         $parameterProvider Parameter provider
     * @param boolean                   $strict            Throw exceptions for non-existent keys
     * @param RuntimeParameterBagLogger $logger            Logger
     */
    public function __construct(ParameterProviderInterface $parameterProvider, $strict = true, RuntimeParameterBagLogger $logger = null)
    {
        parent::__construct();

        $this->parameterProvider = $parameterProvider;
        $this->strict = $strict;
        $this->logger = $logger;
    }

    /**
     * @see Symfony\Component\DependencyInjection\ParameterBag\ParameterBag::all()
     */
    public function all()
    {
        $this->initialize();

        return parent::all();
    }

    /**
     * @see Symfony\Component\DependencyInjection\ParameterBag\ParameterBag::get()
     */
    public function get($name)
    {
        $this->initialize();

        try {
            return parent::get($name);
        } catch (ParameterNotFoundException $e) {
            if (null !== $this->logger) {
                $this->logger->log($e->getMessage());
            }

            if ($this->strict) {
                throw $e;
            } else {
                return null;
            }
        }
    }

    /**
     * @see Symfony\Component\DependencyInjection\ParameterBag\ParameterBag::has()
     */
    public function has($name)
    {
        $this->initialize();

        return parent::has($name);
    }

    /**
     * Return whether the RuntimeParameterBag is strict and will throw an
     * exception when getting a nonexistent key.
     *
     * @return boolean
     */
    public function isStrict()
    {
        return $this->strict;
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

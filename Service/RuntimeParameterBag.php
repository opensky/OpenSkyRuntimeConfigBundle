<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Service;

use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;

class RuntimeParameterBag extends FrozenParameterBag
{
    private $initialized = false;
    private $parameterProvider;
    private $strict;

    /**
     * Constructor.
     *
     * @param ParameterProvider $parameterProvider Parameter provider
     * @param boolean           $strict            Throw exceptions for non-existent keys
     */
    public function __construct(ParameterProviderInterface $parameterProvider, $strict = true)
    {
        parent::__construct();

        $this->parameterProvider = $parameterProvider;
        $this->strict = $strict;
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

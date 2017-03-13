<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Tests\DependencyInjection;

use OpenSky\Bundle\RuntimeConfigBundle\DependencyInjection\OpenSkyRuntimeConfigExtension;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OpenSkyRuntimeConfigExtensionTest extends TestCase
{
    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testLoadShouldThrowExceptionUnlessProviderIsSpecified()
    {
        $loader = new OpenSkyRuntimeConfigExtension();

        $loader->load(array(array()), new ContainerBuilder());
    }

    /**
     * @dataProvider provideProviderOptions
     */
    public function testLoadShouldSetProviderAlias($provider)
    {
        $container = new ContainerBuilder();
        $loader = new OpenSkyRuntimeConfigExtension();

        $config = array('provider' => $provider);

        $loader->load(array($config), $container);

        $this->assertEquals($provider, (string) $container->getAlias('opensky.runtime_config.provider'));
    }

    public function provideProviderOptions()
    {
        return array(
            array('provider.real'),
            array('provider.other'),
        );
    }

    public function testLoadShouldInjectContainerIfCascadeEnabled()
    {
        $container = new ContainerBuilder();
        $loader = new OpenSkyRuntimeConfigExtension();

        $config = array(
            'provider' => 'provider.real',
            // Cascade is enabled by default
        );

        $loader->load(array($config), $container);

        $calls = $container->getDefinition('opensky.runtime_config')->getMethodCalls();

        $this->assertEquals(1, count($calls));
        $this->assertEquals('setContainer', $calls[0][0]);
        $this->assertEquals('service_container', (string) $calls[0][1][0]);
    }

    public function testLoadShouldNotInjectContainerIfCascadeDisabled()
    {
        $container = new ContainerBuilder();
        $loader = new OpenSkyRuntimeConfigExtension();

        $config = array(
            'provider' => 'provider.real',
            'cascade' => false,
        );

        $loader->load(array($config), $container);

        $this->assertEquals(0, count($container->getDefinition('opensky.runtime_config')->getMethodCalls()));
    }

    public function testLoadShouldAddLoggerArgumentIfLoggingEnabled()
    {
        $container = new ContainerBuilder();
        $loader = new OpenSkyRuntimeConfigExtension();

        $config = array(
            'provider' => 'provider.real',
            // Logging is enabled by default
        );

        $loader->load(array($config), $container);

        $this->assertEquals('opensky.runtime_config.logger', (string) $container->getDefinition('opensky.runtime_config')->getArgument(2));
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testLoadShouldNotAddLoggerArgumentIfLoggingDisabled()
    {
        $container = new ContainerBuilder();
        $loader = new OpenSkyRuntimeConfigExtension();

        $config = array(
            'provider' => 'provider.real',
            'logging' => array(
                'enabled' => false,
            ),
        );

        $loader->load(array($config), $container);

        $container->getDefinition('opensky.runtime_config')->getArgument(2);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testLoadShouldThrowExceptionForInvalidLogLevel()
    {
        $loader = new OpenSkyRuntimeConfigExtension();

        $config = array(
            'provider' => 'provider.real',
            'logging' => array(
                'level' => 'foo',
            ),
        );

        $loader->load(array($config), new ContainerBuilder());
    }

    /**
     * @dataProvider provideValidLogLevels
     */
    public function testLoadShouldSetValidLogLevels($level)
    {
        $container = new ContainerBuilder();
        $loader = new OpenSkyRuntimeConfigExtension();

        $config = array(
            'provider' => 'provider.real',
            'logging' => array(
                'level' => $level,
            ),
        );

        $loader->load(array($config), $container);

        $this->assertEquals($level, $container->getParameter('opensky.runtime_config.logger.level'));
    }

    public function provideValidLogLevels()
    {
        return array_map(
            function($level){ return (array) $level; },
            get_class_methods(LoggerInterface::class)
        );
    }
}

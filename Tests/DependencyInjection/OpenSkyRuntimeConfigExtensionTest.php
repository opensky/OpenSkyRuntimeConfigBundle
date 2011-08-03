<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Tests\DependencyInjection;

use OpenSky\Bundle\RuntimeConfigBundle\DependencyInjection\OpenSkyRuntimeConfigExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OpenSkyRuntimeConfigExtensionTest extends \PHPUnit_Framework_TestCase
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
     * @dataProvider provideProviderAndStrictnessOptions
     */
    public function testLoadShouldSetProviderAliasAndStrictness($provider, $strict)
    {
        $container = new ContainerBuilder();
        $loader = new OpenSkyRuntimeConfigExtension();

        $config = array(
            'provider' => $provider,
            'strict' => $strict,
        );

        $loader->load(array($config), $container);

        $this->assertEquals($provider, (string) $container->getAlias('opensky.runtime_config.provider'));
        $this->assertEquals($strict, $container->getParameter('opensky.runtime_config.strict'));
    }

    public function provideProviderAndStrictnessOptions()
    {
        return array(
            array('provider.real', true),
            array('provider.other', false),
        );
    }

    public function testLoadShouldAddLoggerArgumentIfLoggingEnabled()
    {
        $container = new ContainerBuilder();
        $loader = new OpenSkyRuntimeConfigExtension();

        $config = array(
            'provider' => 'provider.real',
            'logging' => array(
                'enabled' => true,
            ),
        );

        $loader->load(array($config), $container);

        $this->assertEquals('opensky.runtime_config.logger', (string) $container->getDefinition('opensky.runtime_config')->getArgument(2));
    }

    /**
     * @expectedException OutOfBoundsException
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
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
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
            get_class_methods('Symfony\Component\HttpKernel\Log\LoggerInterface')
        );
    }
}

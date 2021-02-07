<?php

namespace OpenSky\Bundle\RuntimeConfigBundle\Tests\DependencyInjection;

use OpenSky\Bundle\RuntimeConfigBundle\DependencyInjection\OpenSkyRuntimeConfigExtension;
use OpenSky\Bundle\RuntimeConfigBundle\Util\LogUtil;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OpenSkyRuntimeConfigExtensionTest extends TestCase
{
    public function testLoadShouldCreatePrivateServiceDefinition()
    {
        $loader = new OpenSkyRuntimeConfigExtension();
        $container = new ContainerBuilder();

        $config = [
            'provider' => 'provider.real',
        ];

        $loader->load([$config], $container);

        $this->assertTrue($container->hasDefinition('opensky.runtime_config.parameter_bag'));
        $this->assertFalse($container->getDefinition('opensky.runtime_config.parameter_bag')->isPublic());
    }

    public function testLoadShouldCreatePublicAlias()
    {
        $loader = new OpenSkyRuntimeConfigExtension();
        $container = new ContainerBuilder();

        $config = [
            'provider' => 'provider.real',
        ];

        $loader->load([$config], $container);

        $this->assertFalse($container->hasDefinition('opensky.runtime_config'));
        $this->assertTrue($container->hasAlias('opensky.runtime_config'));
        $this->assertTrue($container->getAlias('opensky.runtime_config')->isPublic());
    }

    public function testLoadShouldThrowExceptionUnlessProviderIsSpecified()
    {
        $this->expectException(InvalidConfigurationException::class);

        $loader = new OpenSkyRuntimeConfigExtension();

        $loader->load([[]], new ContainerBuilder());
    }

    /**
     * @dataProvider provideProviderOptions
     */
    public function testLoadShouldSetProviderAlias($provider)
    {
        $container = new ContainerBuilder();
        $loader = new OpenSkyRuntimeConfigExtension();

        $config = ['provider' => $provider];

        $loader->load([$config], $container);

        $this->assertEquals($provider, (string) $container->getAlias('opensky.runtime_config.provider'));
    }

    public function provideProviderOptions()
    {
        return [
            ['provider.real'],
            ['provider.other'],
        ];
    }

    public function testLoadShouldInjectContainerIfCascadeEnabled()
    {
        $container = new ContainerBuilder();
        $loader = new OpenSkyRuntimeConfigExtension();

        $config = [
            'provider' => 'provider.real',
            // Cascade is enabled by default
        ];

        $loader->load([$config], $container);

        $calls = $container->findDefinition('opensky.runtime_config')->getMethodCalls();

        $this->assertEquals(1, count($calls));
        $this->assertEquals('setContainer', $calls[0][0]);
        $this->assertEquals('service_container', (string) $calls[0][1][0]);
    }

    public function testLoadShouldNotInjectContainerIfCascadeDisabled()
    {
        $container = new ContainerBuilder();
        $loader = new OpenSkyRuntimeConfigExtension();

        $config = [
            'provider' => 'provider.real',
            'cascade' => false,
        ];

        $loader->load([$config], $container);

        $this->assertEquals(0, count($container->findDefinition('opensky.runtime_config')->getMethodCalls()));
    }

    public function testLoadShouldAddLoggerArgumentIfLoggingEnabled()
    {
        $container = new ContainerBuilder();
        $loader = new OpenSkyRuntimeConfigExtension();

        $config = [
            'provider' => 'provider.real',
            // Logging is enabled by default
        ];

        $loader->load([$config], $container);

        $this->assertEquals('opensky.runtime_config.logger', (string) $container->findDefinition('opensky.runtime_config')->getArgument(2));
    }

    public function testLoadShouldNotAddLoggerArgumentIfLoggingDisabled()
    {
        $this->expectException(\OutOfBoundsException::class);

        $container = new ContainerBuilder();
        $loader = new OpenSkyRuntimeConfigExtension();

        $config = [
            'provider' => 'provider.real',
            'logging' => [
                'enabled' => false,
            ],
        ];

        $loader->load([$config], $container);

        $container->findDefinition('opensky.runtime_config')->getArgument(2);
    }

    public function testLoadShouldThrowExceptionForInvalidLogLevel()
    {
        $this->expectException(InvalidConfigurationException::class);

        $loader = new OpenSkyRuntimeConfigExtension();

        $config = [
            'provider' => 'provider.real',
            'logging' => [
                'level' => 'foo',
            ],
        ];

        $loader->load([$config], new ContainerBuilder());
    }

    /**
     * @dataProvider provideValidLogLevels
     */
    public function testLoadShouldSetValidLogLevels($level)
    {
        $container = new ContainerBuilder();
        $loader = new OpenSkyRuntimeConfigExtension();

        $config = [
            'provider' => 'provider.real',
            'logging' => [
                'level' => $level,
            ],
        ];

        $loader->load([$config], $container);

        $this->assertEquals($level, $container->getParameter('opensky.runtime_config.logger.level'));
    }

    /**
     * @return array
     */
    public function provideValidLogLevels()
    {
        return array_map(function($level) {
            return [$level];
        }, LogUtil::getValidLogLevels());
    }
}

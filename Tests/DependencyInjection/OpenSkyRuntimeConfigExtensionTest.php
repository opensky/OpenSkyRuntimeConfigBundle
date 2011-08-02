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
}

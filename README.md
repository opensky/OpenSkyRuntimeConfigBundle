# RuntimeConfigBundle

This bundle provides a way to inject parameters into services at runtime by
exposing a RuntimeParameterBag service, which functions exactly like Symfony2's
own ParameterBags.

As-is, Symfony2's service container is compiled and cached to disk, which makes
it difficult to inject dynamic parameters. By exposing a ParameterBag service,
we can inject values returned from its `get()` method into other services.

One reason you might want support for dynamic parameters would be to implement
feature flags/flippers, as are used by [GitHub][] and [Flickr][]. More info on
the history behind this bundle may be found on the [symfony-devs][] mailing list.

  [GitHub]: https://github.com/blog/677-how-we-deploy-new-features
  [Flickr]: http://code.flickr.com/blog/2009/12/02/flipping-out/
  [symfony-devs]: https://groups.google.com/forum/#!msg/symfony-devs/DKSoai_CWX4/qJVeFhL6GzAJ

## Note

OpenSkyRuntimeConfigBundle 1.x is compatible with Symfony 2.x, but is no longer maintained.
OpenSkyRuntimeConfigBundle 2.x is compatible with Symfony 3.x and will be actively maintained.
Please upgrade to OpenSkyRuntimeConfigBundle 2.x as soon as possible.

## Installation

### Step 1: Download OpenSkyRuntimeConfigBundle using composer:

Require the bundle with composer: 

```sh
$ composer require opensky/runtime-config-bundle
```

### Step 2: Enable the bundle

Enable the bundle in the kernel:

```php
<?php
 
// app/AppKernel.php
 
public function registerBundles()
{
    return [
        new OpenSky\Bundle\RuntimeConfigBundle\OpenSkyRuntimeConfigBundle(),
    ];
}
```

### Step 3: Configure your application's config.yml

The RuntimeParameterBag may be configured with the following:

```yml
# app/config/config.yml
 
opensky_runtime_config:
    provider: parameter.provider.service
    cascade:  true
    logging:
        enabled: true
        level:   debug
```

These settings are explained below:

 * `provider`: A service implementing ParameterProviderInterface. If you are
    using Doctrine ORM as your datasource, this could be an EntityRepository.
 * `cascade`: If true, calls to `get()` will cascade to the service container
    when the parameter is undefined in the runtime configuration. This will
    not change the behavior of `has()` or `all()`, which always only consider
    parameters from the runtime configuration provider.
 * `logging.enabled`: Whether to enable logging access to undefined parameters,
    regardless of whether service container cascading is enabled. If you are
    using Monolog, logs will be sent to the "opensky.runtime_config" channel.
 * `logging.level`: Log level to use (should be a LoggerInterface method).

Note: when using `cascade`, it's a good idea to define default values for your
runtime configuration parameters in your service container. This will help
avoid an undesirable ParameterNotFoundException if you happen to fetch a
parameter that is not yet defined in your runtime configuration.

## Injecting Parameters

Consider the scenario where "my.service" depends on a dynamic parameter
"my.service.enabled".

Runtime parameters may be conveniently injected using Symfony's expression language syntax:

XML is recommended for services defined within a bundle:

```xml
<?xml version="1.0" ?>
 
<!-- src/AppBundle/Resources/config/services.xml -->
 
<services>
    <service id="my.service" class="MyService">
        <argument type="expression">service('opensky.runtime_config').get('my.service.enabled')</argument>
    </service>
</services>
```

YAML is recommended for application-wide services:

```yaml
# app/config/services.yml
 
services:
 
    my.service:
        class: MyService
        arguments:
            - "@=service('opensky.runtime_config').get('my.service.enabled')"
```

### Cascade Mode

If you have enabled cascade mode, `get()` will attempt to fetch undefined
runtime parameters from the service container before throwing an exception.

Building upon the previous XML example, this would look like:

```xml
<?xml version="1.0" ?>
 
<!-- src/AppBundle/Resources/config/services.xml -->
 
<parameters>
    <parameter key="my.service.enabled">false</parameter>
</parameters>
 
<services>
    <service id="my.service" class="MyService">
        <argument type="expression">service('opensky.runtime_config').get('my.service.enabled')</argument>
    </service>
</services>
```

In this example, `get('my.services.enabled')` would return false even if the
parameter was not defined in the runtime configuration. This is a safe way to
introduce new parameters, which might not yet be available from your provider
at the time of deployment.

Note: parameters sourced from the runtime configuration provider are **not**
resolved for placeholder syntax (i.e. "%reference%"), unlike those defined in
the service container.

## Recipe: Interpreting Parameter Values as YAML

If you're using Doctrine ORM (or any database) to hold your parameters, you will
likely implement a CRUD interface to define and edit parameters via an admin
controller in your application.

Additionally, this allows us to add custom behavior to our ParameterProvider.
For instance, we can use Symfony2's YAML component to interpret parameter values
stored in the database as strings.

Consider the following Entity:

```php
<?php
 
// src/MyBundle/Entity/Parameter.php
 
namespace MyBundle\Entity\Parameter;
 
use Doctrine\ORM\Mapping as ORM;
use OpenSky\Bundle\RuntimeConfigBundle\Entity\Parameter as BaseParameter;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContext;
use Symfony\Component\Yaml\Inline;
use Symfony\Component\Yaml\ParserException;
 
/**
 * @ORM\Entity(repositoryClass="MyBundle\Entity\ParameterRepository")
 * @ORM\Table(
 *     name="parameters",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="name_unique", columns={"name"})
 *     }
 * )
 * @Assert\Callback(methods={"validateValueAsYaml"})
 */
class Parameter extends BaseParameter
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;
  
    public function getId()
    {
        return $this->id;
    }
  
    public function validateValueAsYaml(ExecutionContext $context)
    {
        try {
            Inline::load($this->value);
        } catch (ParserException $e) {
            $context->setPropertyPath($context->getPropertyPath() . '.value');
            $context->addViolation('This value is not valid YAML syntax', array(), $this->value);
        }
    }
}
```

Several things are happening here:

 * We must map an ID field, as the base Parameter class only defines essential
   name and value fields.
 * The base class defines assertions for name and value fields (in groups, which
   can be easily disabled); however, the mapped superclass does not define a
   unique constraint on the name, so that is necessary.
 * A callback assertion is used to check that the value property is valid YAML.

The above Entity class is complemented by the following EntityRepository, which
serves as the ParameterProvider for the RuntimeParameterBag:

```php
<?php
 
// src/MyBundle/Entity/ParameterRepository.php
 
namespace MyBundle\Entity\Parameter;
 
use OpenSky\Bundle\RuntimeConfigBundle\Entity\ParameterRepository as BaseParameterRepository;
use Symfony\Component\Yaml\Inline;
 
class ParameterRepository extends BaseParameterRepository
{
    public function getParametersAsKeyValueHash()
    {
        return array_map(
            function($v){ return Inline::load($v); },
            parent::getParametersAsKeyValueHash()
        );
    }
}
```

The base ParameterRepository already fetches name/value pairs from the database
via a DQL query. Using `array_map()`, we can easily interpret those values
through the same YAML component method.

Note: although we validate the Entity, it's possible that a value might have
been manually altered in the database and contain invalid YAML when parameters
are fetched for provision. If this is a concern, you may want to gracefully
handle thrown ParserExceptions within `getParametersAsKeyValueHash()`.

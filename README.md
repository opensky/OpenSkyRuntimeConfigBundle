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

## Installation

### Submodule Creation

Add RuntimeConfigBundle to your `vendor/` directory:

    $ git submodule add https://github.com/opensky/OpenSkyRuntimeConfigBundle.git vendor/bundles/OpenSky/Bundle/RuntimeConfigBundle

### Class Autoloading

Register the "OpenSky" namespace prefix in your project's `autoload.php`:

    # app/autoload.php

    $loader->registerNamespaces(array(
        'OpenSky' => __DIR__'/../vendor/bundles',
    ));

### Application Kernel

Add RuntimeConfigBundle to the `registerBundles()` method of your application
kernel:

    public function registerBundles()
    {
        return array(
            new OpenSky\Bundle\RuntimeConfigBundle\OpenSkyRuntimeConfigBundle(),
        );
    }

## Configuration

### RuntimeConfigBundle Extension

The RuntimeParameterBag may be configured with the following:

    # app/config/config.yml

    opensky_runtime_config:
        provider: parameter.provider.service
        strict:   true
        logging:
            enabled: true
            level:   debug

These settings are explained below:

 * `provider`: A service implementing ParameterProviderInterface. If you are
    using Doctrine ORM as your datasource, this could be an EntityRepository.
 * `strict`: Whether exceptions should be thrown for accessing nonexistent
    parameters. If disabled, `null` will be returned instead.
 * `logging.enabled`: Whether to enable logging of nonexistent parameter access.
    If you are using Monolog, logs will be sent to the "opensky.runtime_config"
    channel.
 * `logging.level`: Log level to use (should be a LoggerInterface method).

Note: for development environments, you'd likely want to keep strict mode on.
But in production, it may be preferable to fail gracefully by logging errors and
safely handling `null` return values. For instance, if you expected a parameter
"my.service.enabled", which was undefined, `null` could still be interpreted by
your service as "I should disable my functionality".

## Injecting Parameters ##

Consider the scenario where "my.service" depends on a dynamic parameter
"my.service.enabled".

Runtime parameters may be conveniently injected by abusing the anonymous service
syntax in XML configurations:

    # Resources/config/my_service.xml

    <service id="my.service" class="MyService">
        <argument type="service">
            <service class="stdClass" factory-service="opensky.runtime_config" factory-method="get">
                <argument>my.service.enabled</argument>
            </service>
        </argument>
    </service>

Unfortunately, the YAML format does not yet support defining anonymous services.
Parameter injection is still possible, but more verbose:

    # MyBundle/Resources/config/my_service.yml

    my.service:
        class: MyService
        arguments:
            - @my.service.enabled

    my.service.enabled:
        public: false
        class: stdClass
        factory_service: opensky.runtime_config
        factory_method: get
        arguments:
            - my.service.enabled

Note: in both cases (anonymous and labeled services), Symfony2 requires that we
define the class on our service definition. The above examples use "stdClass" as
an arbitrary placeholder to satisfy CheckDefinitionValidityPass. In reality, our
service is simply a means to lazily load our parameter. The value returned by
`get()` can be anything (e.g. object, scalar, array).

## Recipe: Interpretting Parameter Values as YAML ##

If you're using Doctrine ORM (or any database) to hold your parameters, you will
likely implement a CRUD interface to define and edit parameters via an admin
controller in your application.

Additionally, this allows us to add custom behavior to our ParameterProvider.
For instance, we can using Symfony2's YAML component to interpret parameter
values stored in the database as strings.

Consider the following Entity:

    # MyBundle/Entity/Parameter.php

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

Several things are happening here:

 * We must map an ID field, as the base Parameter class only defines essential
   name and value fields.
 * The base class defines assertions for name and value fields (in groups, which
   can be easily disabled); however, the mapped superclass does not define a
   unique constraint on the name, so that is necessary.
 * A callback assertion is used to check that the value property is valid YAML.

The above Entity class is complemented by the following EntityRepository, which
serves as the ParameterProvider for the RuntimeParameterBag:

    # MyBundle/Entity/ParameterRepository.php

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

The base ParameterRepository already fetches name/value pairs from the database
via a DQL query. Using `array_map()`, we can easily interpret those values
through the same YAML component method.

Note: although we validate the Entity, it's possible that a value might have
been manually altered in the database and contain invalid YAML when parameters
are fetched for provision. If this is a concern, you may want to gracefully
handle thrown ParserExceptions within `getParametersAsKeyValueHash()`.

<?php

/*
 * This file is part of the IdealTech Redis bundle.
 *
 * (c) Ideal Technology <http://www.idealtechnology.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IdealTech\RedisBundle\DependencyInjection;

use UnexpectedValueException,
    RuntimeException;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\Config\FileLocator,
    Symfony\Component\DependencyInjection\Definition,
    Symfony\Component\DependencyInjection\Reference,
    Symfony\Component\DependencyInjection\Loader;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * The Redis bundle configuration
 *
 * @author Guillaume Coguiec <gcoguiec@idealtechnology.net>
 */
class IdealTechRedisExtension extends Extension
{
    /**
     * {@inheritDoc}
     *
     * @param array            $configs   The inherited configurations
     * @param ContainerBuilder $container The container builder
     *
     * @return null
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $this->configureClients($config, $container);

        $loader->load('redis.xml');
    }

    /**
     * Configure the predis clients depending on the configuration tree
     *
     * @param array            $config    The configuration array
     * @param ContainerBuilder $container The container builder
     * @return null
     */
    protected function configureClients(array $config, ContainerBuilder $container)
    {
        if (!class_exists('\Predis\Client')) {
            throw new RuntimeException('RedisBundle: Predis client class cannot be found (missing dependency ?).');
        }

        if (!count($clients = $config['clients'])) {
            throw new UnexpectedValueException('RedisBundle: You need to define at least one client
                inside the "ideal_tech_redis.clients" configuration section.');
        }

        foreach ($clients as $name => $parameters) {
            if (!count($servers = $parameters['servers'])) {
                continue;
            }

            $definition = new Definition('%ideal_tech_redis.client.class%', $parameters['servers']);
            $definition->setPublic(true);
            $container->setDefinition(sprintf('ideal_tech_redis.clients.%s', $name), $definition);
        }

        if (!$container->hasDefinition($id = sprintf('ideal_tech_redis.clients.%s', $default = $config['default_client']))) {
            throw new UnexpectedValueException('RedisBundle: You need a default client named "%s" in your configuration or
                change it in "ideal_tech_redis.default_client" setting.');
        }

        $container->setAlias('ideal_tech_redis.default_client', $id);
        $container->setParameter('ideal_tech_redis.clients', $clients);

        $this->remapParametersNamespaces($config, $container, array(
            'default_client' => 'ideal_tech_redis.default_client',
        ));
    }

    /**
     * Remap a set of parameters
     *
     * @param array            &$config    Configuration array
     * @param ContainerBuilder &$container Container instance
     * @param array            $map        Mapping array
     *
     * @author Jordi Boggiano <j.boggiano@seld.be>
     * @author Guillaume Coguiec <gcoguiec@idealtechnology.net>
     *
     * @return null
     */
    protected function remapParameters(array &$config, ContainerBuilder &$container, array $map)
    {
        foreach ($map as $name => $newName) {
            if (isset($config[$name])) {
                $container->setParameter($newName, $config[$name]);
            }
        }
    }

    /**
     * Remap an entire namespace of parameters
     *
     * @param array            &$config    Configuration array
     * @param ContainerBuilder &$container Container instance
     * @param array            $namespaces Namespaces array
     *
     * @author Jordi Boggiano <j.boggiano@seld.be>
     * @author Guillaume Coguiec <gcoguiec@idealtechnology.net>
     *
     * @return null
     */
    protected function remapParametersNamespaces(array &$config, ContainerBuilder &$container, array $namespaces)
    {
        foreach ($namespaces as $ns => $map) {
            if ($ns) {
                if (!array_key_exists($ns, $config)) {
                    continue;
                }
                $namespaceConfig = $config[$ns];
            } else {
                $namespaceConfig = $config;
            }

            if (is_array($map)) {
                if ($config === $namespaceConfig) {
                    $this->remapParameters($namespaceConfig, $container, $map);
                } else {
                    $this->remapParametersNamespaces($namespaceConfig, $container, $map);
                }
            } else {
                if (!is_array($namespaceConfig)) {
                    $container->setParameter($map, $namespaceConfig);
                    continue;
                }
                if (false === strpos($map, '%s')) {
                    $container->setParameter($map, $namespaceConfig);
                    continue;
                }
                foreach ($namespaceConfig as $name => $value) {
                    $container->setParameter(sprintf($map, $name), $value);
                }
            }
        }
    }
}
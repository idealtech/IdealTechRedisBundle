<?php

/*
 * This file is part of the IdealTech Redis bundle.
 *
 * (c) Ideal Technology <http://www.idealtechnology.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IdealTech\RedisBundle\Redis;

use UnexpectedValueException;

use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * The redis client manager
 *
 * @author Guillaume Coguiec <gcoguiec@idealtechnology.net>
 */
class ClientManager extends ContainerAware implements ClientManagerInterface
{
    protected $options;

    /**
     * Constructor.
     *
     * @param array $clients The clients name collection
     */
    public function __construct(array $options)
    {
        $this->options = array_merge(array(
            'default_client' => null,
            'clients' => array(),
        ), $options);
    }

    /**
     * {@inheritDoc}
     */
    public function get($name = null)
    {
        if (null === $name) {
            throw new UnexpectedValueException('You need to enter a valid client name [%s]', implode(array_keys($this->clients)));
        }

        if (!$this->container->has($id = sprintf('ideal_tech_redis.clients.%s', $name))) {
            throw new UnexpectedValueException(sprintf('There is no "%s" client name inside the pool.', $name));
        }

        return $this->container->get($id);
    }

    /**
     * Returns the default client
     *
     * @return Predis\Client The default client
     */
    public function getDefault()
    {
        return $this->get($this->options['default_client']);
    }

    /**
     * Returns the complete clients collection
     *
     * @return array The clients
     */
    public function getClients()
    {
        $manager = $this;
        return array_map(function ($client) use ($manager) {
            return $manager->get($client);
        }, $this->clients);
    }

    /**
     * Checks if a specific client exists.
     *
     * @param string $name The client name
     *
     * @return Boolean If the client exists or not
     */
    public function hasClient($name)
    {
        return !$this->container->has($id = sprintf('ideal_tech_redis.clients.%s', $name));
    }
}
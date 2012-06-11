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

/**
 * The redis client manager interface
 *
 * @author Guillaume Coguiec <gcoguiec@idealtechnology.net>
 */
interface ClientManagerInterface
{
    /**
     * Return a specific client based on its name.
     *
     * @param string $name The client name
     *
     * @return Predis\Client The client
     *
     * @throws UnexpectedValueException If the manager failed to find the client.
     */
    function get($name = 'default');
}
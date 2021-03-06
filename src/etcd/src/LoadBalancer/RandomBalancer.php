<?php

namespace Mix\Etcd\LoadBalancer;

use Mix\Etcd\Service\Service;

/**
 * Class RandomBalancer
 * @package Mix\Etcd\LoadBalancer
 */
class RandomBalancer implements LoadBalancerInterface
{

    /**
     * Invoke
     * @param Service[id] $services
     * @return Service
     */
    public function invoke(array $services)
    {
        return $services[array_rand($services)];
    }

}

<?php

namespace Sins\Config;

use Pimple\Container;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class PimpleParameterBag extends ParameterBag
{
    private $container;

    public function __construct(Container $container, array $parameters = array())
    {
        $this->container = $container;
        $this->add($parameters);
    }

    public function get($name)
    {
        $name = strtolower($name);

        if ($this->has($name)) {
            return $this->parameters[$name];
        }

        if (!isset($this->container[$name])) {
            throw new ParameterNotFoundException($name);
        }

        return $this->container[$name];
    }
}

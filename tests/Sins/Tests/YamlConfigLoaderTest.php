<?php

namespace Sins\Tests;

use Pimple\Container;
use Sins\Config\YamlFileLoader;
use Symfony\Component\Config\Exception\FileLoaderLoadException;
use Symfony\Component\Config\FileLocator;

/**
 * @coversDefaultClass \Sins\Config\YamlFileLoader
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    protected $pimple;
    protected $locator;

    protected function setUp()
    {
        $this->locator = new FileLocator(sprintf('%s/Resources', __DIR__));

        $this->pimple = new Container();
        $this->pimple['container_value'] = sha1(uniqid());
    }

    protected function tearDown()
    {
        unset($this->pimple);
        unset($this->locator);
    }

    public function testNoInclude()
    {
        $loader = new YamlFileLoader($this->locator, $this->pimple);

        $a = $loader->load('parameters_noinclude.yml');

        $loader->parameterBag->resolve();
        $parameters = $loader->parameterBag->all();

        $this->assertArrayHasKey('parameter2', $parameters);
        $this->assertArrayHasKey('parameter3', $parameters);
        $this->assertArrayHasKey('parameter_arr', $parameters);

        $this->assertEquals($parameters['parameter2'], 'value2_new');
        $this->assertEquals($parameters['parameter3'], $this->pimple['container_value']);
        $this->assertEquals($parameters['parameter_arr'], array('key1' => 'value1_new', 'key2' => 'value2_new'));
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException
     */
    public function testNoIncludeNonexisting()
    {
        $loader = new YamlFileLoader($this->locator, $this->pimple);

        $loader->load('parameters_noinclude_bad.yml');

        $loader->parameterBag->resolve();
        $parameters = $loader->parameterBag->all();

        $this->assertArrayHasKey('parameter3', $parameters);
    }

    public function testInclude()
    {
        $loader = new YamlFileLoader($this->locator, $this->pimple);

        $loader->load('parameters_include.yml');

        $loader->parameterBag->resolve();
        $parameters = $loader->parameterBag->all();

        $this->assertArrayHasKey('parameter1', $parameters);
        $this->assertArrayHasKey('parameter2', $parameters);
        $this->assertArrayHasKey('parameter3', $parameters);
        $this->assertArrayHasKey('parameter_arr', $parameters);

        $this->assertEquals($parameters['parameter1'], 'value1_base');
        $this->assertEquals($parameters['parameter2'], 'value2_include');
        $this->assertEquals($parameters['parameter3'], $this->pimple['container_value'].'include');
        $this->assertEquals($parameters['parameter_arr'], array('key1' => 'value1_inc', 'key2' => 'value2_inc', 'key3' => 'value1_base'));
    }

    public function testIncludeNonexisting()
    {
        $this->expectException(FileLoaderLoadException::class);

        $loader = new YamlFileLoader($this->locator, $this->pimple);

        $loader->load('parameters_include_noresource.yml');

        $loader->parameterBag->resolve();
        $parameters = $loader->parameterBag->all();

        $this->assertArrayHasKey('parameter1', $parameters);
    }
}

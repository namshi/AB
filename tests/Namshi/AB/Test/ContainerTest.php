<?php

namespace Namshi\AB\Test;

use Namshi\AB\Container;
use Namshi\AB\Test;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->container = new Container();
    }
    
    public function testInstantiationOfTheContainer()
    {
        $this->assertInstanceOf('Namshi\AB\Container', $this->container); 
    }
    
    public function testTheContainerAcceptsTestsFromTheConstructor()
    {
        $container = new Container(array(new Test(1), new Test(2)));
        
        $this->assertCount(2, $container->getAll()); 
    }
    
    public function testTheContainerIsCountable()
    {
        $container = new Container(array(new Test(1), new Test(2)));
        
        $this->assertCount(2, $container); 
    }
    
    public function testIfTwoTestsHaveTheSameNameTheContainerOnlyRegistersOneOfThem()
    {
        $container = new Container(array(new Test(1), new Test(1)));
        
        $this->assertCount(1, $container->getAll()); 
        $this->assertCount(1, $container->getAll()); 
    }
    
    public function testYouCanUseTheContainerAsAnArray()
    {
        $this->container->add(new Test('myTest'));
        
        $this->assertInstanceOf('Namshi\AB\Test', $this->container['myTest']);
    }
    
    public function testYouCanUnsetTestsAsAnArray()
    {
        $this->container->add(new Test('myTest'));
        
        $this->assertCount(1, $this->container);
        
        unset($this->container['myTest']);
        
        $this->assertCount(0, $this->container);
    }
    
    public function testYouCanSetTestsAsAnArray()
    {
        $test = new Test('mySetTest');
        $this->container[$test->getName()] = $test;
        
        $this->assertCount(1, $this->container);
        $this->assertEquals('mySetTest', $this->container['mySetTest']->getName());
    }
    
    public function testYouCanIterateOverTheContainer()
    {
        $test = new Test('mySetTest');
        $this->container[$test->getName()] = $test;
        
        foreach ($this->container as $test) {
            $this->assertEquals('mySetYesy', $test->getName());
        }
    }
}
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
    
    public function testYouCanCreateTestsFromThContainerItself()
    {
        $container = new Container();
        $container->createTest('myTest', array(1, 2), array(1 => 2));
        
        $this->assertCount(1, $container->getAll()); 
        $this->assertInstanceOf('Namshi\AB\Test', $container['myTest']); 
        $this->assertEquals(2, $container['myTest']->get(1)); 
    }
    
    public function testTheContainerIsCountable()
    {
        $container = new Container(array(new Test(1), new Test(2)));
        
        $this->assertCount(2, $container); 
    }
    
    public function testYouCanDisableAllTestsFromTheContainer()
    {
        $test1 = new Test(1);
        $test2 = new Test(2);
        $container = new Container(array($test1, $test2));

        $this->assertFalse($test1->isDisabled());
        $this->assertFalse($test2->isDisabled());
        
        $container->disableTests();
        
        $this->assertTrue($test1->isDisabled());
        $this->assertTrue($test2->isDisabled());
    }
    
    public function testYouCanRunAllTestsFromTheContainer()
    {
        $test1 = new Test(1, array(1));
        $test2 = new Test(2, array(1));
        $container = new Container(array($test1, $test2));
        
        $this->assertFalse($test1->hasRun());
        $this->assertFalse($test2->hasRun());
        
        $container->runTests();
        
        $this->assertTrue($test1->hasRun());
        $this->assertTrue($test2->hasRun());
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
            $this->assertEquals('mySetTest', $test->getName());
        }
    }
    
    public function testYouCanSeedOverTheContainer()
    {
        $resultsAbc4    = array();
        $resultsAb      = array();
        $tries          = 100;
        $container      = new Container(array(), 12);
        
        for ($i = 0; $i < $tries; $i++) {
            $test   = new Test('abc4', array('a' => 1, 'b' => 2, 'c' => 1, 'd' => 1));
            $container->add($test);
            
            $testAb = new Test('cd123jkbkjtbt', array('a' => 1, 'b' => 2, 'c' => 1, 'd' => 1));
            $container->add($testAb);
            
            $resultsAbc4[]  = $container['abc4']->getVariation();
            $resultsAb[]    = $container['cd123jkbkjtbt']->getVariation();
        }

        $this->assertCount(1, array_unique($resultsAbc4));
        $this->assertEquals(27996520 - 12, $test->getSeed());

        $this->assertCount(1, array_unique($resultsAb));
        $this->assertEquals(215869794 - 12, $testAb->getSeed());
        
        $this->assertNotEquals($testAb->getVariation(), $test->getVariation());
    }
}

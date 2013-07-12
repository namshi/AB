<?php

namespace Namshi\AB\Test;

use Namshi\AB\Test;

class TestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Namshi\AB\Test
     */
    public function getTest($name = 'myTest', array $variations = array('a' => 0, 'b' => 1))
    {
        return new Test($name, $variations);
    }
    
    public function testTheTestsFirstArgumentIsItsName()
    {
        $this->assertEquals('myTest', $this->getTest()->getName());
    }
    
    public function testTheTestCanHaveMultipleVariations()
    {
        $this->assertCount(2, $this->getTest()->getVariations());
    }
    
    public function testCountingTheVariations()
    {
        $this->assertCount(2, $this->getTest());
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testValidatingTheVariations()
    {
        $this->getTest('myTest', array('a' => 'b'));
    }
    
    public function testTheTestIsEnabledByDefault()
    {
        $this->assertTrue($this->getTest()->isEnabled());
        $this->assertFalse($this->getTest()->isDisabled());
    }
    
    public function testDisablingTheTest()
    {        
        for ($i = 0; $i < 100; $i++) {
            $test = $this->getTest('myTest', array('a' => 0, 'b' => 1));
            $test->disable();

            $this->assertFalse($test->isEnabled());
            $this->assertTrue($test->isDisabled());
            $test->run();
            
            $this->assertEquals('a', $test->getVariation());
        }
    }
    
    /**
     * @expectedException BadMethodCallException
     */
    public function testGettingAVariationOfATestThatHasntRunThrowsAnException()
    {
        $this->getTest()->getVariation();
    }
    
    public function testCheckingIfATestHasRun()
    {
        $test = $this->getTest();
        
        $this->assertFalse($test->hasRun());
        
        $test->run();
        
        $this->assertTrue($test->hasRun());
        
        $test->hasRun(false);
        
        $this->assertFalse($test->hasRun());
        
        $test->hasRun(true);
        
        $this->assertTrue($test->hasRun());
    }
    
    public function testGettingTheVariationOfATestWithAVariationWithProbability100()
    {
        $test = $this->getTest();
        
        $test->run();
        
        for ($i = 0; $i < 1000; $i++) {
            $this->assertEquals('b', $test->getVariation());
        }
    }
    
    public function testGettingTheVariationOfATestWithOnlyOneVariation()
    {
        $test = $this->getTest('test', array('a' => 2));
        
        $test->run();
        
        for ($i = 0; $i < 1000; $i++) {
            $this->assertEquals('a', $test->getVariation());
        }
    }
    
    public function testGettingTheVariationOfATestWithSplitOddsBetweenTwoVariations()
    {
        $tries  = 100000;
        $test   = $this->getTest('test', array('a' => 1, 'b' => 1));
        $test->run();
        $counts = array('a' => 0, 'b' => 0);
        
        for ($i = 0; $i < $tries; $i++) {
            $counts[$test->getVariation()] += 1;
        }
        
        $aProbability = $counts['a'] / $tries;
        $bProbability = $counts['b'] / $tries;

        $this->assertTrue($aProbability > 0.49);
        $this->assertTrue($aProbability < 0.51);
        $this->assertTrue($bProbability > 0.49);
        $this->assertTrue($bProbability < 0.51);        
    }
    
    public function testGettingTheVariationOfATestWithIrregularOddsOfVariations()
    {
        $tries  = 100000;
        $test   = $this->getTest('test', array('a' => 1, 'b' => 2));
        $test->run();
        $counts = array('a' => 0, 'b' => 0);
        
        for ($i = 0; $i < $tries; $i++) {
            $counts[$test->getVariation()] += 1;
        }
        
        $aProbability = $counts['a'] / $tries;
        $bProbability = $counts['b'] / $tries;

        $this->assertTrue($aProbability > 0.32);
        $this->assertTrue($aProbability < 0.34);
        $this->assertTrue($bProbability > 0.66);
        $this->assertTrue($bProbability < 0.68);
    }
}
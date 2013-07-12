<?php

namespace Namshi\AB\Test;

use Namshi\AB\Test;

class TestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Namshi\AB\Test
     */
    public function getTest($name = 'myTest', array $variations = array('a' => 0, 'b' => 1), $trackingName = null, array $parameters = array())
    {
        return new Test($name, $variations, $trackingName, $parameters);
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
            $test->getVariation();
            
            $this->assertEquals('a', $test->getVariation());
        }
    }
    
    public function testCheckingIfATestHasRun()
    {
        $test = $this->getTest();
        
        $this->assertFalse($test->hasRun());
        
        $test->getVariation();
        
        $this->assertTrue($test->hasRun());
        
        $test->hasRun(0);
        
        $this->assertFalse($test->hasRun());
        
        $test->hasRun(1);
        
        $this->assertTrue($test->hasRun());
    }
    
    public function testGettingTheVariationOfATestWithAVariationWithProbability100()
    {
        $test = $this->getTest();
        
        for ($i = 0; $i < 1000; $i++) {
            $this->assertEquals('b', $test->getVariation());
        }
    }
    
    public function testGettingTheVariationOfATestWithOnlyOneVariation()
    {
        $test = $this->getTest('test', array('a' => 2));
        
        for ($i = 0; $i < 1000; $i++) {
            $this->assertEquals('a', $test->getVariation());
        }
    }
    
    /**
     * @expectedException BadMethodCallException
     */
    public function testRunningATestWithoutVariationThrowsAnException()
    {
        $this->getTest('name', array(), array('a' => 'myParam'))->getVariation();
    }
    
    public function testRetrievingTheTrackingNameOfTheTest()
    {
        $test = $this->getTest();
        
        $this->assertEquals('myTest', $test->getTrackingName());
        
        $test = $this->getTest('a', array(), 'b');
        
        $this->assertEquals('b', $test->getTrackingName());
        
        $test = $this->getTest('a', array(1));
        $test->getVariation('b');
        
        $this->assertEquals('b', $test->getTrackingName());
    }
    
    public function testTheTestCanHaveParameters()
    {
        $test = $this->getTest('name', array(1), null, array('a' => 'myParam'));
        
        $this->assertCount(1, $test->getParameters());
        $this->assertEquals('myParam', $test->get('a'));
        $this->assertNull($test->get('nonExistingParam'));
        
        $test->getVariation(null, array('b' => 12, 'a' => 11));
        
        $this->assertCount(2, $test->getParameters());
        $this->assertEquals(12, $test->get('b'));
        $this->assertEquals(11, $test->get('a'));
    }
    
    public function testGettingTheVariationOfATestWithSplitOddsBetweenTwoVariations()
    {
        $tries  = 100000;
        $counts = array('a' => 0, 'b' => 0);
        
        for ($i = 0; $i < $tries; $i++) {
            $test   = $this->getTest('test', array('a' => 1, 'b' => 1));
            $test->getVariation();
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
        $counts = array('a' => 0, 'b' => 0);
        
        for ($i = 0; $i < $tries; $i++) {
            $test   = $this->getTest('test', array('a' => 1, 'b' => 2));
            $test->getVariation();
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
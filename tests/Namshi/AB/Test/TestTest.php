<?php

namespace Namshi\AB\Test;

use Namshi\AB\Test;

class TestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \Namshi\AB\Test
     */
    public function getTest($name = 'myTest', array $variations = array('a' => 0, 'b' => 1), array $parameters = array())
    {
        return new Test($name, $variations, $parameters);
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
    
    public function testTheTestCanHaveParameters()
    {
        $test = $this->getTest('name', array(1), array('a' => 'myParam'));
        
        $this->assertCount(1, $test->getParameters());
        $this->assertEquals('myParam', $test->get('a'));
        $this->assertNull($test->get('nonExistingParam'));
        
        $test->getVariation(null);
        
        $this->assertCount(1, $test->getParameters());
        $this->assertEquals('myParam', $test->get('a'));
        
        $test->set('b', null);
        $this->assertNull($test->get('b'));
    }
    
    public function testIfYouSeedTheTestItAlwaysGeneratesTheSameVariation()
    {
        $results = array();
        $tries   = 10000;
        
        for ($i = 0; $i < $tries; $i++) {
            $test       = $this->getTest('test', array('a' => 1, 'b' => 2, 'c' => 1, 'd' => 1));
            $test->setSeed(12);
            $results[]  = $test->getVariation();
        }

        $this->assertCount(1, array_unique($results));
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

    public function testSettingGaExperimentId()
    {
        $test = $this->getTest('test', ['a'=>1,'b'=>1], ['expId'=>'xyz']);
        $this->assertEquals('xyz',Test::getGoogleAnalyticsExperimentId());

        Test::setGoogleAnalyticsExperimentId('123');
        $this->assertEquals('123',Test::getGoogleAnalyticsExperimentId());
    }

    public function testSettingGaExperimentVariantId()
    {
        $params = ['a'=>0,'b'=>2];
        $test = $this->getTest('test', ['a'=>1,'b'=>1], $params);
        $variation = $test->getVariation();
        $this->assertEquals($params[$variation],Test::getGoogleAnalyticsExperimentVariant());
    }
}
<?php

namespace Namshi\AB\Test\Variation;

use Namshi\AB\Variation\Odd;

class OddTest extends \PHPUnit_Framework_TestCase
{
    public function testAnOddFrom1To100IsAlwaysMatched()
    {
        $odd = new Odd(100, 0, 100);
        
        for ($i = 1; $i <= 100; $i++) {
            $this->assertTrue($odd->matches($i), sprintf('Odd doesnt match value %d', $i));
        }
    }
    
    public function testAnOddFrom0To0IsAlwaysMatched()
    {
        $odd = new Odd(0, 0, 0);
        
        for ($i = 1; $i <= 100; $i++) {
            $this->assertFalse($odd->matches($i), sprintf('Odd doesnt match value %d', $i));
        }
    }
    
    public function testMatchingOddWithMultipleRandomNumbers()
    {
        $odd = new Odd(50, 0, 50);
        
        $this->assertTrue($odd->matches(1));
        $this->assertTrue($odd->matches(50));
        $this->assertTrue($odd->matches(35));
        $this->assertFalse($odd->matches(0));
        $this->assertFalse($odd->matches(51));
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testTheValueMustBeAnInteger()
    {
        new Odd('aaa');
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testTheValueMustBeAPositiveNumber()
    {
        new Odd(-30);
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testTheValueMustBeLowerThan100()
    {
        new Odd(101);
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testTheMinMustBeAnInteger()
    {
        new Odd(0, 'a');
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testTheMinMustBeAPositiveNumber()
    {
        new Odd(0, -30);
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testTheMinMustBeLowerThan100()
    {
        new Odd(0, 101);
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testTheMaxMustBeAnInteger()
    {
        new Odd(0, 0, 'a');
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testTheMaxMustBeAPositiveNumber()
    {
        new Odd(0, 0, -30);
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testTheMaxMustBeLowerThan100()
    {
        new Odd(0, 0, 101);
    }
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testTheMaxMustAlwaysBeHigherOrEqualThanTheMin()
    {
        new Odd(0, 2, 1);
    }
}
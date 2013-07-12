<?php

namespace Namshi\AB\Test;

use Namshi\AB\Test;

class TestTest extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->test = new Test('myTest');
    }
    
    public function testTheTestsFirstArgumentIsItsName()
    {
        $this->assertEquals('myTest', $this->test->getName());
    }
}
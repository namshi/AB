<?php

namespace Namshi\AB;

use Namshi\AB\Test;
use ArrayAccess;
use Countable;

/**
 * Class used wrap a collection of tests.
 */
class Container implements ArrayAccess, Countable
{
    protected $tests = array();
    protected $seed;
    
    /**
     * Constructor
     * 
     * @param array $tests
     * @param int $seed
     */
    public function __construct(array $tests = array(), $seed = null)
    {
        foreach ($tests as $test) {
            $this->add($test);
        }
        
        if ($seed) {
            $this->setSeed($seed);
        }
    }
    
    /**
     * Get the seed to be passed to each test.
     * 
     * @return int
     */
    public function getSeed()
    {
        return $this->seed;
    }

    /**
     * Sets the seed to be passed to each test.
     * 
     * @param int $seed
     */
    public function setSeed($seed)
    {
        $this->seed = (int) $seed;
        
        foreach($this->getAll() as $test) {            
            $test->setSeed($this->calculateTestSeed($test));
        }
    }
    
    protected function calculateTestSeed(Test $test)
    {
        $seed = '';
        
        foreach (str_split($test->getName()) as $letter) {
            $seed .= is_numeric($letter) ? $letter : ord($letter) - 96;
        }
        
        return $seed;
    }
    
    /**
     * Returns all the tests registered in the container.
     * 
     * @return array
     */
    public function getAll()
    {
        return $this->tests;
    }
    
    /**
     * Returns a test with the name $test.
     * 
     * @param string $test
     * @return \Namshi\AB\Test
     */
    public function get($test)
    {
        return $this[$test];
    }
    
    /**
     * Adds a new $test.
     * 
     * @param \Namshi\AB\Test $test
     */
    public function add(Test $test)
    {
        if ($this->getSeed()) {
            $test->setSeed($this->calculateTestSeed($test));
        }
        
        $this->tests[$test->getName()] = $test;
    }
    
    /**
     * Checks whether a test is registered.
     * 
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return (bool) isset($this->tests[$offset]);
    }
    
    /**
     * Returns a test, or null if the test was not found.
     * 
     * @param string $offset
     * @return Namshi\AB\Test|null
     */
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->tests[$offset];
        }
        
        return null;
    }
    
    /**
     * Unregisters a test.
     * 
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->tests[$offset]);
        }
    }
    
    /**
     * Registers a test.
     * 
     * @param string $offset
     * @param Namshi\AB\Test $value
     */
    public function offsetSet($offset, $value)
    {
        $this->tests[$offset] = $value;
    }
    
    /**
     * Returns how many tests have been registered in this container.
     * 
     * @return int
     */
    public function count()
    {
        return count($this->getAll());
    }
}
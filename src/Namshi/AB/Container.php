<?php

namespace Namshi\AB;

use Namshi\AB\Test;
use ArrayAccess;
use Countable;
use IteratorAggregate;
use ArrayIterator;

/**
 * Class used wrap a collection of tests.
 */
class Container implements ArrayAccess, Countable, IteratorAggregate
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
            $test->setSeed($this->calculateTestSeed($seed, $test));
        }
    }
    
    /**
     * Convenient method to disable all the tests registered with this container
     * at once.
     */
    public function disableTests()
    {
        foreach ($this->getAll() as $test) {
            $test->disable();
        }
    }
    
    /**
     * Convenient method to run all the tests registered with this container at
     * once.
     */
    public function runTests()
    {
        foreach ($this->getAll() as $test) {
            $test->getVariation();
        }
    }
    
    /**
     * Creates, registers and returns a test with the given parameters.
     * 
     * @param string $name
     * @param array $variations
     * @param string $trackingName
     * @param array $parameters
     * @return Test
     */
    public function createTest($name, array $variations = array(), array $parameters = array())
    {
        $this->add(new Test($name, $variations, $parameters));
        
        return $this[$name];
    }
    
    /**
     * Calculates a seed for the given $test, mixing the global seed and a
     * numerical representation of the test name.
     * 
     * @param int $globalSeed
     * @param \Namshi\AB\Test $test
     * @return int
     */
    protected function calculateTestSeed($globalSeed, Test $test)
    {
        $seed = '';
        
        foreach (str_split(preg_replace("/[^A-Za-z0-9 ]/", '', $test->getName())) as $letter) {
            $seed .= is_numeric($letter) ? $letter : ord($letter) - 96;
        }
        
        $seed = (int) $seed;

        if ($seed > $globalSeed) {
            return $seed - $globalSeed;
        }
        
        return $globalSeed - $seed;
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
            $test->setSeed($this->calculateTestSeed($this->getSeed(), $test));
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
    
    /**
     * Returns the iterator to use to iterate over the container.
     * 
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->tests);
    }
}

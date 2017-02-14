<?php

namespace Namshi\AB;

use Countable;
use InvalidArgumentException;
use BadMethodCallException;

/**
 * Class used to represent an AB test.
 */
class Test implements Countable
{
    use GaExperimentTrait;

    protected $name;
    protected $variations   = array();
    protected $isEnabled    = true;
    protected $hasRun       = false;
    protected $variation;
    protected $parameters   = array();
    protected $seed;
    
    const ERROR_TEST_RAN_WITHOUT_VARIATIONS         = "You are trying to run a test without specifying its variations";
    const ERROR_GET_VARIATION_BEFORE_RUNNING_TEST   = "You must run() the test before getting its variation";
    
    /**
     * Creates a test with the given $name and the specified $variations.
     * 
     * Variations must have an absolute value, not a percentage; for example,
     * - a: 100
     * - b: 100
     * 
     * means that both variations have 50% of probability.
     * 
     * @param string $name
     * @param array $variations
     * @param array $parameters
     */
    public function __construct($name, array $variations = array(), array $parameters = array())
    {
        $this->setName($name);
        $this->setVariations($variations);
        $this->setParameters($parameters);
    }
    
    /**
     * Returns the name of the test.
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the test's $name.
     * 
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * Returns how many variations the test contains.
     * 
     * @return int
     */
    public function count()
    {
        return count($this->variations);
    }
    
    /**
     * Returns the variations of this test.
     * 
     * @return array
     */
    public function getVariations()
    {
        return $this->variations;
    }

    /**
     * Sets the $variations of this test.
     * 
     * @param array $variations
     */
    public function setVariations(array $variations)
    {
        $this->validateVariations($variations);
        $this->variations = $variations;
    }

    /**
     * Gets the seed for this test.
     * 
     * @return int
     */
    public function getSeed()
    {
        return $this->seed;
    }

    /**
     * Sets the seed for this test.
     * 
     * @param int $seed
     */
    public function setSeed($seed)
    {
        if (!$this->hasRun()) {
            $this->seed = (int) $seed;
        }
    }
    
    /**
     * Disables the test: this is useful when, for example, you want to exclude
     * this test to run for specific request (for example, bots).
     */
    public function disable()
    {
        $this->isEnabled = false;
    }
    
    /**
     * Checks whether the test is enabled or not.
     * 
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * Checks whether the test is disabled or not.
     * 
     * @return bool
     */
    public function isDisabled()
    {
        return !$this->isEnabled();
    }
    
    /**
     * Returns the variation of this test.
     * 
     * You must run the test before getting the variation, else a 
     * BadMethodCallException is thrown.
     * If the test is disabled, the first variation will always be returned,
     * even if its odd is set to 0.
     * Sets the GoogleAnalyticsExperimentVariation if the a parameter with a key equal to the variation exists.
     * 
     * @return string
     */
    public function getVariation()
    {        
        if (!$this->hasRun()) {
            $this->run();
        }
        
        if ($this->isDisabled()) {
            $variations = array_keys($this->getVariations());

            return array_shift($variations);
        }

        // If the variant has a GA Experiment Variant Id
        if($this->get($this->variation) !== NULL){
            // Set the Google Analytics Experiment Variant Id
            static::setGoogleAnalyticsExperimentVariant($this->get($this->variation));
        }

        return $this->variation;
    }
    
    /**
     * Checks whether the test has run or not.
     * 
     * @param bool $ran
     * @return bool
     */
    public function hasRun($ran = null)
    {
        if (!is_null($ran)) {
            $this->hasRun = (bool) $ran;
        }
        
        return (bool) $this->hasRun;
    }
    
    /**
     * Gets the parameters for this test.
     * 
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Sets the parameters for this test.
     *
     * If an 'expId' parameter is defined, the googleAnalyticsExperimentId is set accordingly
     * Any parameters values that have keys matching a variation key are interpreted as google experiment variant id's
     * See https://developers.google.com/analytics/solutions/experiments-server-side#store-user for how to determine
     * what the variation id's should be set to
     * 
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;

        if($this->get('expId')){
            // Set the Google Analytics ExperimentId
            static::setGoogleAnalyticsExperimentId($this->get('expId'));
        }
    }
    
    /**
     * Returns a test's parameter.
     * 
     * @param string $parameter
     * @return mixed
     */
    public function get($parameter)
    {
        if (isset($this->parameters[$parameter])) {
            return $this->parameters[$parameter];
        }
    }
    
    /**
     * Returns a test's parameter.
     * 
     * @param string $parameter
     * @param mixed $value
     */
    public function set($parameter, $value)
    {
        $this->parameters[$parameter] = $value;
    }
    
    /**
     * Runs the test.
     */
    public function run()
    {
        if (!$this->count()) {
            throw new BadMethodCallException(self::ERROR_TEST_RAN_WITHOUT_VARIATIONS);
        }
        
        $this->hasRun(true);
        $this->calculateVariation();
    }
    
    /**
     * Validates an array of variations.
     * All the variations must have an integer value.
     * 
     * @param array $variations
     * @throws InvalidArgumentException
     */
    protected function validateVariations(array $variations)
    {
        array_walk($variations, function($variation) {
            if (!is_int($variation)) {
                throw new InvalidArgumentException;
            }
        });
    }
    
    /**
     * Calculates the variation of this test.
     */
    protected function calculateVariation()
    {
        if ($this->getSeed()) {
            mt_srand($this->getSeed());
        }

        $sum    = 0;
        $max    = array_sum($this->getVariations());
        $random = mt_rand(1, $max);
        
        foreach ($this->getVariations() as $variation => $odd) {
            $sum += $odd;
            
            if($random <= $sum) {
                $this->variation = $variation;
                
                return;
            }
        }
    }

}

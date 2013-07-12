<?php

namespace Namshi\AB;

use Namshi\AB\Variation\Odd;
use Countable;
use InvalidArgumentException;
use BadMethodCallException;

/**
 * Class used to represent an AB test.
 */
class Test implements Countable
{
    protected $name;
    protected $trackingName;
    protected $variations   = array();
    protected $isEnabled    = true;
    protected $hasRun       = false;
    protected $variation;
    protected $parameters = array();
    
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
     * @param string $ttrackingName
     * @param array $parameters
     */
    public function __construct($name, array $variations = array(), $trackingName = null, array $parameters = array())
    {
        $this->setName($name);
        $this->setVariations($variations);
        $this->setParameters($parameters);
        $this->setTrackingName($trackingName);
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
     * Runs the test.
     * 
     * @param string $trackingName
     * @param array $parameters
     * @return bool
     */
    public function run($trackingName = null, array $parameters = array())
    {
        if (!$this->count()) {
            throw new BadMethodCallException(self::ERROR_TEST_RAN_WITHOUT_VARIATIONS);
        }
        
        if ($trackingName) {
            $this->setTrackingName($trackingName);
        }
        
        $this->setParameters(array_merge($this->getParameters(), $parameters));
        $this->hasRun(true);
        $this->calculateVariation();
    }
    
    /**
     * Returns the variation of this test.
     * 
     * You must run the test before getting the variation, else a 
     * BadMethodCallException is thrown.
     * If the test is disabled, the first variation will always be returned,
     * even if its odd is set to 0.
     * 
     * @return string
     * @throws BadMethodCallException
     */
    public function getVariation()
    {
        if ($this->hasRun()) {
            if ($this->isDisabled()) {
                $variations = array_keys($this->getVariations());

                return array_shift($variations);
            }
            
            return $this->variation;
        }
        
        throw new BadMethodCallException(self::ERROR_GET_VARIATION_BEFORE_RUNNING_TEST);
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
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
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
     * Gets the tracking name of this test.
     * 
     * @return string
     */
    public function getTrackingName()
    {
        return $this->trackingName ?: $this->getName();
    }

    /**
     * Sets the $trackingName of this test.
     * 
     * @param string $trackingName
     */
    public function setTrackingName($trackingName)
    {
        $this->trackingName = $trackingName;
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
     * 
     * The variation is calculated generating Odds for all the variations of the
     * test and a random number. If the number matches the odd, the variation
     * with that odd is picked.
     */
    protected function calculateVariation()
    {
        $random = mt_rand(1, 100);
        $odds   = $this->calculateOdds();
        
        foreach ($odds as $variation => $odd) {
            if ($odd->matches($random)) {
                $this->variation = $variation;
            }
        }
    }
    
    /**
     * Given that variations are given with absolute weight (ie. a:1, b:2 means
     * a has 33% and b 66%), this function converts the absolute odd into a
     * percentage, putting it into an Odd object that also registers minimum
     * and maximum for that particular odd.
     * 
     * Minimum and maximum are then used to check whether a random-generated
     * number matches the odd. Following the example above, our odds will
     * become:
     * - a:
     *      - absolute value:   1
     *      - value:            33
     *      - minimum:          0
     *      - maximum:          33
     * -b:
     *      - absolute value:   2
     *      - value:            66
     *      - minimum:          34
     *      - maximum:          100
     * 
     * which means that if you generate a random number between 1 and 100 you
     * can then check where it lies among the odds (10 would nail a, 50 would
     * nail b).
     * 
     * @return array
     */
    protected function calculateOdds()
    {
        $odds = array();
        $min  = 0;
        $max  = 0;
        
        foreach ($this->getVariations() as $variation => $odd) {
            $odd = (int) number_format($odd * 100 / array_sum($this->getVariations()), 0);
            $max += $odd;
            $odds[$variation] = new Odd($odd, $min, $max);
            $min += $odd;
        }
        
        $variations                     = array_keys($odds);
        $lastVariation                  = end($variations);
        $odds[$lastVariation]->setMax(100);
 
        return $odds;
    }
}
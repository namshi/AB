<?php

namespace Namshi\AB;

use Countable;
use InvalidArgumentException;
use BadMethodCallException;

class Test implements Countable
{
    protected $name;
    protected $variations   = array();
    protected $isEnabled    = true;
    protected $hasRun       = false;
    
    public function __construct($name, array $variations = array())
    {
        $this->setName($name);
        $this->setVariations($variations);
    }
    
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
    
    public function count()
    {
        return count($this->variations);
    }
    
    public function getVariations()
    {
        return $this->variations;
    }

    public function setVariations(array $variations)
    {
        $this->validateVariations($variations);
        $this->variations = $variations;
    }
    
    public function disable()
    {
        $this->isEnabled = false;
    }
    
    public function isEnabled()
    {
        return $this->isEnabled;
    }
    
    public function isDisabled()
    {
        return !$this->isEnabled();
    }
    
    public function run()
    {
        $this->hasRun(true);
    }
    
    public function getVariation()
    {
        if ($this->hasRun()) {
            if ($this->isDisabled()) {
                $variations = array_keys($this->getVariations());

                return array_shift($variations);
            }
            
            return $this->calculateVariation();
        }
        
        throw new BadMethodCallException("You must run() the test before getting its variation");
    }
    
    public function hasRun($ran = null)
    {
        if (is_bool($ran)) {
            $this->hasRun = $ran;
        }
        
        return $this->hasRun;
    }
    
    protected function validateVariations(array $variations)
    {
        array_walk($variations, function($variation) {
            if (!is_int($variation)) {
                throw new InvalidArgumentException;
            }
        });
    }
    
    protected function calculateVariation()
    {
        $random = mt_rand(1, 100);
        $odds   = $this->calculateOddsAsPercentage();
        
        foreach ($odds as $variation => $odd) {
            if ($random > $odd['min'] && $random <= $odd['max']) {
                return $variation;
            }
        }
    }
    
    protected function calculateOddsAsPercentage()
    {
        $odds = array();
        $min  = 0;
        $max  = 0;
        
        foreach ($this->getVariations() as $variation => $odd) {
            $odd = number_format($odd * 100 / array_sum($this->getVariations()), 0);
            $max += $odd;
 
            $odds[$variation] = array(
                'value' => $odd,
                'min'   => $min,
                'max'   => $max,
            );
            
            $min += $odd;
            
        }
        
        $variations                     = array_keys($odds);
        $lastVariation                  = end($variations);
        $odds[$lastVariation]['max']    = 100;
 
        return $odds;
    }
}
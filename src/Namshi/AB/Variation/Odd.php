<?php

namespace Namshi\AB\Variation;

use InvalidArgumentException;

/**
 * This class represents a variation's odd and its context.
 * 
 * A variation with odd = 50, min = 25 and max = 75 means that this odd is
 * matched against a number higher than 25, up to 75.
 */
class Odd
{
    const ERROR_INVALID_VALUE       = "A odd's value must be between 0 and 100, %s given";
    const ERROR_VALUE_NOT_INTEGER   = "An odd's value, min and max must be integers between 0 and 100, %s given";
    const ERROR_MAX_LOWER_THAN_MIN  = "The given max (%s) is lower than the min (%s)";
    const MIN                       = 0;
    const MAX                       = 100;
    
    protected $value;
    protected $min = 0;
    protected $max = 0;
    
    /**
     * Constructor
     * 
     * @param int $value
     * @param int $min
     * @param int $max
     */
    public function __construct($value = 0, $min= 0, $max = 0)
    {        
        $this->setValue($value);
        $this->setMin($min);
        $this->setMax($max);
    }
    
    /**
     * Checks whether the $number is in between the limits of this odd.
     * An odd matches any number that is higher that its min and lower (or
     * equals) its max.
     * 
     * @param type $number
     * @return type
     */
    public function matches($number)
    {        
        return (bool) ($number > $this->getMin() && $number <= $this->getMax());
    }
    
    /**
     * Returns the value of the odd.
     * 
     * @return int
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the odd's value.
     * 
     * @param int $value
     */
    public function setValue($value)
    {
        $this->validateValue($value);
        $this->value = (int) $value;
    }

    /**
     * Returns the min of the odd.
     * 
     * @return int
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * Sets the $min of the odd.
     * 
     * @param int $min
     */
    public function setMin($min)
    {
        $this->validateValue($min);
        $this->min = (int) $min;
    }

    /**
     * Returns the max of the odd.
     * 
     * @return int
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * Sets the $max of the odd.
     * 
     * @param int $max
     */
    public function setMax($max)
    {
        $this->validateMax($max);
        $this->max = (int) $max;
    }
    
    /**
     * Validates a value that has to be an integer between 0 and 100.
     * 
     * @param int $value
     * @throws InvalidArgumentException
     */
    protected function validateValue($value)
    {
        if (!is_int($value)) {
            throw new InvalidArgumentException(sprintf(self::ERROR_VALUE_NOT_INTEGER, $value));
        }
        
        if ($value > 100 || $value < 0) {
            throw new InvalidArgumentException(sprintf(self::ERROR_INVALID_VALUE, $value));
        }
    }
    
    /**
     * Validate the $max parameter of the odd, which has to be lower than its
     * min.
     * 
     * @param int $max
     * @throws InvalidArgumentException
     */
    protected function validateMax($max)
    {
        $this->validateValue($max);
        
        if ($max < $this->getMin()) {
            throw new InvalidArgumentException(sprintf(self::ERROR_MAX_LOWER_THAN_MIN, $max, $this->getMin()));
        }
    }
}
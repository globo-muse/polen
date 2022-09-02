<?php

namespace ProfilePress\Core\Membership\Services;

use ProfilePressVendor\Brick\Math\BigDecimal;
use ProfilePressVendor\Brick\Math\RoundingMode;

class Calculator
{
    /** @var BigDecimal */
    protected $result;

    protected $scale;

    protected $roundingMode;

    public function __construct($leftOperand, $scale = 8, $roundingMode = RoundingMode::HALF_UP)
    {
        $leftOperand = empty($leftOperand) ? '0' : $leftOperand;

        $this->result = BigDecimal::of((string)$leftOperand);

        $this->scale = $scale;

        $this->roundingMode = $roundingMode;
    }

    public function plus($rightOperand)
    {
        $this->result = $this->result->plus($rightOperand);

        return $this;
    }

    public function minus($rightOperand)
    {
        $this->result = $this->result->minus($rightOperand);

        return $this;
    }

    public function multipliedBy($rightOperand)
    {
        $this->result = $this->result->multipliedBy($rightOperand);

        return $this;
    }

    public function dividedBy($rightOperand)
    {
        $this->result = $this->result->dividedBy(
            $rightOperand,
            $this->scale,
            $this->roundingMode
        );

        return $this;
    }

    public function isNegativeOrZero()
    {
        return $this->result->isNegativeOrZero();
    }

    public function isNegative()
    {
        return $this->result->isNegative();
    }

    public function isEqualTo($val)
    {
        return $this->result->isEqualTo($val);
    }

    public function isZero()
    {
        return $this->result->isZero();
    }

    public function isGreaterThan($val)
    {
        return $this->result->isGreaterThan($val);
    }

    public function isLessThan($val)
    {
        return $this->result->isLessThan($val);
    }

    public function isGreaterThanZero()
    {
        return $this->result->isGreaterThan('0');
    }

    public function toScale($scale, $roundingMode = null)
    {
        $roundingMode = $roundingMode ?? $this->roundingMode;

        $this->result = $this->result->toScale($scale, $roundingMode);

        return $this;
    }

    /**
     * @return BigDecimal
     */
    public function result()
    {
        return $this->result;
    }

    public function val()
    {
        return (string)$this->result;
    }

    /**
     * @return self
     */
    public static function init($leftOperand)
    {
        return new self($leftOperand);
    }
}
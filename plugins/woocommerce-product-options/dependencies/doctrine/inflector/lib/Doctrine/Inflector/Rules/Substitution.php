<?php

declare (strict_types=1);
namespace Barn2\Plugin\WC_Product_Options\Dependencies\Doctrine\Inflector\Rules;

/** @internal */
final class Substitution
{
    /** @var Word */
    private $from;
    /** @var Word */
    private $to;
    public function __construct(Word $from, Word $to)
    {
        $this->from = $from;
        $this->to = $to;
    }
    public function getFrom() : Word
    {
        return $this->from;
    }
    public function getTo() : Word
    {
        return $this->to;
    }
}
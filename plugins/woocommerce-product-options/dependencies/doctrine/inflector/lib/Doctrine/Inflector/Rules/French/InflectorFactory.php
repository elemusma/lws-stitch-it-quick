<?php

declare (strict_types=1);
namespace Barn2\Plugin\WC_Product_Options\Dependencies\Doctrine\Inflector\Rules\French;

use Barn2\Plugin\WC_Product_Options\Dependencies\Doctrine\Inflector\GenericLanguageInflectorFactory;
use Barn2\Plugin\WC_Product_Options\Dependencies\Doctrine\Inflector\Rules\Ruleset;
/** @internal */
final class InflectorFactory extends GenericLanguageInflectorFactory
{
    protected function getSingularRuleset() : Ruleset
    {
        return Rules::getSingularRuleset();
    }
    protected function getPluralRuleset() : Ruleset
    {
        return Rules::getPluralRuleset();
    }
}

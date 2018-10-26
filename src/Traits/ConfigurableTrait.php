<?php

namespace Php\Support\Traits;

use Php\Support\Exceptions\InvalidParamException;

trait ConfigurableTrait
{

    /**
     * @param array $attributes
     * @param bool  $exceptOnMiss
     *
     * @return $this
     */
    public function configurable(array $attributes, $exceptOnMiss = true)
    {
        foreach ($attributes as $key => $value) {
            if ($exceptOnMiss && !property_exists($this, $key)) {
                throw new InvalidParamException("Property $key is absent at class: " . get_class($this));
            }
            $this->{$key} = $value;
        }

        return $this;
    }
}
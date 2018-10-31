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
    public function configurable(array $attributes, ?bool $exceptOnMiss = true)
    {
        foreach ($attributes as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            } else {
                if ($exceptOnMiss) {
                    throw new InvalidParamException("Property $key is absent at class: " . get_class($this));
                }
            }
        }

        return $this;
    }
}
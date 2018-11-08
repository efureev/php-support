<?php

namespace Php\Support\Traits;

/**
 * Trait DynamicHash
 *
 * @package Php\Support\Traits
 */
trait DynamicHash
{
    /**
     * @var array
     */
    protected $dynamicHashKeys = [];

    /**
     * @param array $keys
     *
     * @return $this
     */
    public function setDynamicHashKeys(array $keys)
    {
        $this->dynamicHashKeys = $keys;

        return $this;
    }

    /**
     * Create Dynamic hash from $value on keys from dynamicHashKeys
     *
     * @param mixed  $value
     * @param string $delimiter
     *
     * @return null|string
     */
    public function dynamicHash($value, string $delimiter = '|'): ?string
    {
        if ($this->dynamicHashKeys) {
            $values = static::buildValuesPack($this->dynamicHashKeys, $value);

            if ($values) {
                return static::hash(implode($delimiter, $values));
            }
        }

        return null;
    }

    /**
     * @param string $value
     *
     * @return string
     */
    protected static function hash(string $value): string
    {
        return md5($value);
    }

    /**
     * @param array $keys
     * @param mixed $value
     *
     * @return array
     */
    protected static function buildValuesPack(array $keys, $value): array
    {
        return array_filter(array_map(function ($key) use ($value) {
            return $value[ $key ] ?? null;
        }, $keys));
    }
}
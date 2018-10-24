<?php

namespace Php\Support\Interfaces;

interface Storage extends BaseStorage
{
    /**
     * Put value to storage by key
     *
     * @param string $key    key of a value
     * @param mixed  $value  data
     * @param array  $params ext-params
     *
     * @return mixed
     */
    public function put(string $key, $value, array $params = []);

    /**
     * Returns Value by Key
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key);

    /**
     * Returns a Collection of rows by key
     *
     * @param string   $key    key of the value
     * @param int|null $limit  Count of rows
     * @param int|null $offset Offset of selection
     *
     * @return mixed
     */
    public function list(string $key, int $limit = null, int $offset = null);

    /**
     * Delete the value by key
     *
     * @param  string $key
     *
     * @return bool
     */
    public function delete(string $key);
}

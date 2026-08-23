<?php

declare(strict_types=1);

namespace Php\Support\Tests\Helpers;

use JsonSerializable;

/**
 * Class JsonModel
 */
class JsonModel implements JsonSerializable
{
    /** @var mixed the tests reassign this with objects too */
    public $data = ['json' => 'serializable'];

    public function jsonSerialize(): mixed
    {
        return $this->data;
    }
}

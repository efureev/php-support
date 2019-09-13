<?php
declare(strict_types=1);

namespace Php\Support\Exceptions;

/**
 * Class MissingConfigException
 *
 * @package Php\Support\Exceptions
 */
class MissingConfigException extends ConfigException
{
    /** @var string|null */
    protected $needKey;

    /**
     * MissingConfigException constructor.
     *
     * @param mixed $config
     * @param string $needKey
     * @param string $message
     */
    public function __construct($config = null, $needKey = null, $message = 'Missing Config')
    {
        $this->needKey = $needKey;

        parent::__construct($message, $config);
    }
}

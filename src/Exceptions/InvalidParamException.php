<?php

namespace Php\Support\Exceptions;

/**
 * Class InvalidParamException
 *
 * @package Php\Support\Exceptions
 */
class InvalidParamException extends \LogicException
{
    /** @var string|null */
    protected $param;

    public function getName()
    {
        return 'Invalid Parameter';
    }

    /**
     * InvalidParamException constructor.
     *
     * @param null|string $param
     * @param null|string $message
     */
    public function __construct(?string $message = null, ?string $param = null)
    {
        $this->param = $param;
        parent::__construct($message ?? sprintf('Invalid Parameter' . ($this->param ? ": %s" : ''), $this->param));
    }

    /**
     * @return null|string
     */
    public function getParam(): ?string
    {
        return $this->param;
    }
}

<?php
declare(strict_types=1);

namespace Php\Support\Exceptions;

use LogicException;

/**
 * Class InvalidParamException
 *
 * @package Php\Support\Exceptions
 */
class InvalidParamException extends LogicException
{
    /** @var string|null */
    protected $param;

    /**
     * InvalidParamException constructor.
     *
     * @param null|string $param
     * @param null|string $message
     */
    public function __construct(?string $message = null, ?string $param = null)
    {
        $this->param = $param;
        parent::__construct($message ?? sprintf('Invalid Parameter' . ($this->param ? ': %s' : ''), $this->param));
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Invalid Parameter';
    }

    /**
     * @return null|string
     */
    public function getParam(): ?string
    {
        return $this->param;
    }
}

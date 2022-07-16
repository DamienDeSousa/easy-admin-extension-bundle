<?php

declare(strict_types=1);

namespace Dades\EasyAdminExtensionBundle\Tests;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\Constraint\IsType;

class TraversableContainsAtLeast extends Constraint
{
    /**
     * @var Constraint
     */
    private $constraint;

    /**
     * @var string
     */
    private $type;

    /**
     * @throws \PHPUnit\Framework\Exception
     */
    public function __construct(string $type, bool $isNativeType = true)
    {
        if ($isNativeType) {
            $this->constraint = new IsType($type);
        } else {
            $this->constraint = new IsInstanceOf(
                $type
            );
        }

        $this->type = $type;
    }

    public function evaluate($other, string $description = '', bool $returnResult = false): ?bool
    {
        $success = false;

        foreach ($other as $item) {
            if ($this->constraint->evaluate($item, '', true)) {
                $success = true;

                break;
            }
        }

        if ($returnResult) {
            return $success;
        }

        if (!$success) {
            $this->fail($other, $description);
        }

        return null;
    }

    public function toString(): string
    {
        return 'contains at least one value of type "' . $this->type . '".';
    }
}
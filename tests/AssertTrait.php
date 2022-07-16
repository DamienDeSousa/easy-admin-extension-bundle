<?php

declare(strict_types=1);

namespace Dades\EasyAdminExtensionBundle\Tests;

trait AssertTrait
{
    public static function assertContainsAtLeastInstancesOf(string $className, iterable $haystack, string $message = ''): void
    {
        static::assertThat(
            $haystack,
            new TraversableContainsAtLeast(
                $className,
                false
            ),
            $message
        );
    }
}

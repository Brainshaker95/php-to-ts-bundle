<?php

declare(strict_types=1);

namespace App\Tests;

use App\Tests\Fixture\Input\NativeTypes;
use Brainshaker95\PhpToTsBundle\Attribute\AsTypeScriptable;
use Brainshaker95\PhpToTsBundle\Exception\AssertionFailedException;
use Brainshaker95\PhpToTsBundle\Tool\Assert;
use Brainshaker95\PhpToTsBundle\Tool\Attribute;
use Exception;
use IteratorAggregate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use stdClass;
use Stringable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Traversable;

use const PHP_INT_MAX;

/**
 * @internal
 */
#[Small]
#[CoversClass(Attribute::class)]
#[CoversClass(Assert::class)]
final class AssertTest extends KernelTestCase
{
    public function testNonEmptyStringNonNullable(): void
    {
        self::assertSame('test', Assert::nonEmptyStringNonNullable('test'));

        $this->expectException(AssertionFailedException::class);

        Assert::nonEmptyStringNonNullable('');
        Assert::nonEmptyStringNonNullable(null);
        Assert::nonEmptyStringNonNullable([]);
        Assert::nonEmptyStringNonNullable(1);
        Assert::nonEmptyStringNonNullable(1.1);
        Assert::nonEmptyStringNonNullable(new stdClass());
    }

    public function testNonEmptyStringNullable(): void
    {
        self::assertSame('test', Assert::nonEmptyStringNullable('test'));
        self::assertNull(Assert::nonEmptyStringNullable(null));

        $this->expectException(AssertionFailedException::class);

        Assert::nonEmptyStringNullable('');
        Assert::nonEmptyStringNullable([]);
        Assert::nonEmptyStringNullable(1);
        Assert::nonEmptyStringNullable(1.1);
        Assert::nonEmptyStringNullable(new stdClass());
    }

    public function testNonNegativeIntegerNonNullable(): void
    {
        self::assertSame(1, Assert::nonNegativeIntegerNonNullable(1));
        self::assertSame(PHP_INT_MAX, Assert::nonNegativeIntegerNonNullable(PHP_INT_MAX));
        self::assertSame(0, Assert::nonNegativeIntegerNonNullable(0));

        $this->expectException(AssertionFailedException::class);

        Assert::nonNegativeIntegerNonNullable(-1);
        Assert::nonNegativeIntegerNonNullable(null);
        Assert::nonNegativeIntegerNonNullable('');
        Assert::nonNegativeIntegerNonNullable([]);
        Assert::nonNegativeIntegerNonNullable(1.1);
        Assert::nonNegativeIntegerNonNullable(new stdClass());
    }

    public function testNonNegativeIntegerNullable(): void
    {
        self::assertSame(1, Assert::nonNegativeIntegerNullable(1));
        self::assertSame(PHP_INT_MAX, Assert::nonNegativeIntegerNullable(PHP_INT_MAX));
        self::assertSame(0, Assert::nonNegativeIntegerNullable(0));
        self::assertNull(Assert::nonNegativeIntegerNullable(null));

        $this->expectException(AssertionFailedException::class);

        Assert::nonNegativeIntegerNullable(-1);
        Assert::nonNegativeIntegerNullable('');
        Assert::nonNegativeIntegerNullable([]);
        Assert::nonNegativeIntegerNullable(1.1);
        Assert::nonNegativeIntegerNullable(new stdClass());
    }

    public function testNonEmptyStringArrayNonNullable(): void
    {
        self::assertSame(['test'], Assert::nonEmptyStringArrayNonNullable(['test']));

        $this->expectException(AssertionFailedException::class);

        Assert::nonEmptyStringArrayNonNullable(['']);
        Assert::nonEmptyStringArrayNonNullable(['test', '']);
        Assert::nonEmptyStringArrayNonNullable(null);
        Assert::nonEmptyStringArrayNonNullable('');
        Assert::nonEmptyStringArrayNonNullable([]);
        Assert::nonEmptyStringArrayNonNullable(1.1);
        Assert::nonEmptyStringArrayNonNullable(new stdClass());
    }

    public function testNonEmptyStringArrayNullable(): void
    {
        self::assertSame(['test'], Assert::nonEmptyStringArrayNullable(['test']));
        self::assertNull(Assert::nonEmptyStringArrayNullable(null));

        $this->expectException(AssertionFailedException::class);

        Assert::nonEmptyStringArrayNullable(['']);
        Assert::nonEmptyStringArrayNullable(['test', '']);
        Assert::nonEmptyStringArrayNullable('');
        Assert::nonEmptyStringArrayNullable([]);
        Assert::nonEmptyStringArrayNullable(1.1);
        Assert::nonEmptyStringArrayNullable(new stdClass());
    }

    public function testInStringArrayNonNullable(): void
    {
        self::assertSame('test', Assert::inStringArrayNonNullable('test', ['test']));
        self::assertSame('', Assert::inStringArrayNonNullable('', ['test', '']));

        $this->expectException(AssertionFailedException::class);

        Assert::inStringArrayNonNullable(null, ['test']);
        Assert::inStringArrayNonNullable('test', ['']);
        Assert::inStringArrayNonNullable('', ['test']);
        Assert::inStringArrayNonNullable([], ['test']);
        Assert::inStringArrayNonNullable(1.1, ['test']);
        Assert::inStringArrayNonNullable(new stdClass(), ['test']);
    }

    public function testInStringArrayNullable(): void
    {
        self::assertSame('test', Assert::inStringArrayNullable('test', ['test']));
        self::assertSame('', Assert::inStringArrayNullable('', ['test', '']));
        self::assertNull(Assert::inStringArrayNullable(null, ['test', '']));

        $this->expectException(AssertionFailedException::class);

        Assert::inStringArrayNullable('test', ['']);
        Assert::inStringArrayNullable('', ['test']);
        Assert::inStringArrayNullable([], ['test']);
        Assert::inStringArrayNullable(1.1, ['test']);
        Assert::inStringArrayNullable(new stdClass(), ['test']);
    }

    public function testInstanceOf(): void
    {
        $object = new stdClass();

        self::assertSame($object, Assert::instanceOf($object, stdClass::class));

        $this->expectException(AssertionFailedException::class);

        self::assertSame($object, Assert::instanceOf($object, Exception::class));
    }

    public function testInterfaceClassStringNonNullable(): void
    {
        self::assertSame(Stringable::class, Assert::interfaceClassStringNonNullable(Stringable::class, Stringable::class));

        $this->expectException(AssertionFailedException::class);

        Assert::interfaceClassStringNonNullable(null, Stringable::class);
        Assert::interfaceClassStringNonNullable('test', Stringable::class);
        Assert::interfaceClassStringNonNullable('', Stringable::class);
        Assert::interfaceClassStringNonNullable([], Stringable::class);
        Assert::interfaceClassStringNonNullable(1.1, Stringable::class);
    }

    public function testInterfaceClassStringNullable(): void
    {
        self::assertSame(Stringable::class, Assert::interfaceClassStringNullable(Stringable::class, Stringable::class));
        self::assertNull(Assert::interfaceClassStringNullable(null, Stringable::class));

        $this->expectException(AssertionFailedException::class);

        Assert::interfaceClassStringNullable('test', Stringable::class);
        Assert::interfaceClassStringNullable('', Stringable::class);
        Assert::interfaceClassStringNullable([], Stringable::class);
        Assert::interfaceClassStringNullable(1.1, Stringable::class);
    }

    public function testInterfaceClassStringArrayNonNullable(): void
    {
        self::assertSame([Stringable::class], Assert::interfaceClassStringArrayNonNullable([Stringable::class], Stringable::class));
        self::assertSame([Traversable::class, IteratorAggregate::class], Assert::interfaceClassStringArrayNonNullable([Traversable::class, IteratorAggregate::class], Traversable::class));

        $this->expectException(AssertionFailedException::class);

        Assert::interfaceClassStringArrayNonNullable([Traversable::class], Traversable::class);
        Assert::interfaceClassStringArrayNonNullable(null, Traversable::class);
        Assert::interfaceClassStringArrayNonNullable('test', Traversable::class);
        Assert::interfaceClassStringArrayNonNullable('', Traversable::class);
        Assert::interfaceClassStringArrayNonNullable([], Traversable::class);
        Assert::interfaceClassStringArrayNonNullable(1.1, Traversable::class);
    }

    public function testInterfaceClassStringArrayNullable(): void
    {
        self::assertSame([Stringable::class], Assert::interfaceClassStringArrayNullable([Stringable::class], Stringable::class));
        self::assertSame([Traversable::class, IteratorAggregate::class], Assert::interfaceClassStringArrayNullable([Traversable::class, IteratorAggregate::class], Traversable::class));
        self::assertNull(Assert::interfaceClassStringArrayNullable(null, Traversable::class));

        $this->expectException(AssertionFailedException::class);

        Assert::interfaceClassStringArrayNullable([Traversable::class], Traversable::class);
        Assert::interfaceClassStringArrayNullable('test', Traversable::class);
        Assert::interfaceClassStringArrayNullable('', Traversable::class);
        Assert::interfaceClassStringArrayNullable([], Traversable::class);
        Assert::interfaceClassStringArrayNullable(1.1, Traversable::class);
    }

    public function testExistingClassAttribute(): void
    {
        Assert::existingClassAttribute(NativeTypes::class, AsTypeScriptable::class);
        Assert::existingClassAttribute(new NativeTypes('', '', '', '', ''), AsTypeScriptable::class);

        $this->expectException(AssertionFailedException::class);

        // @phpstan-ignore-next-line
        Assert::existingClassAttribute('Foo/Bar/Baz', AsTypeScriptable::class);
        Assert::existingClassAttribute(Assert::class, AsTypeScriptable::class);
        Assert::existingClassAttribute(self::class, AsTypeScriptable::class);
    }
}

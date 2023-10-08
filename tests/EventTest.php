<?php

declare(strict_types=1);

namespace App\Tests;

use Brainshaker95\PhpToTsBundle\Event\TsInterfaceGeneratedEvent;
use Brainshaker95\PhpToTsBundle\Event\TsPropertyGeneratedEvent;
use Brainshaker95\PhpToTsBundle\Model\TsInterface;
use Brainshaker95\PhpToTsBundle\Model\TsProperty;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @internal
 *
 * @small
 *
 * @covers \Brainshaker95\PhpToTsBundle\Event\TsInterfaceGeneratedEvent
 * @covers \Brainshaker95\PhpToTsBundle\Event\TsPropertyGeneratedEvent
 */
final class EventTest extends TestCase
{
    public function testTsInterfaceGeneratedEvent(): void
    {
        $eventDispatcher = new EventDispatcher();

        $eventDispatcher->addListener(
            eventName: TsInterfaceGeneratedEvent::class,
            listener: static function (TsInterfaceGeneratedEvent $event): void {
                self::assertInstanceOf(TsInterface::class, $event->tsInterface);

                $event->tsInterface = null;
            },
        );

        $event = $eventDispatcher->dispatch(new TsInterfaceGeneratedEvent(
            tsInterface: new TsInterface('Test'),
            classNode: new Class_('Test'),
        ));

        self::assertNull($event->tsInterface);
        self::assertInstanceOf(Class_::class, $event->classNode);
    }

    public function testTsPropertyGeneratedEvent(): void
    {
        $eventDispatcher = new EventDispatcher();

        $eventDispatcher->addListener(
            eventName: TsPropertyGeneratedEvent::class,
            listener: static function (TsPropertyGeneratedEvent $event): void {
                self::assertInstanceOf(TsProperty::class, $event->tsProperty);

                $event->tsProperty = null;
            },
        );

        $event1 = $eventDispatcher->dispatch(new TsPropertyGeneratedEvent(
            tsProperty: new TsProperty('Test', TsProperty::TYPE_UNKNOWN),
            propertyNode: new Param(new Variable('Test')),
        ));

        $event2 = $eventDispatcher->dispatch(new TsPropertyGeneratedEvent(
            tsProperty: new TsProperty('Test', TsProperty::TYPE_UNKNOWN),
            propertyNode: new Property(
                type: new Identifier('Test'),
                flags: 0,
                props: [],
            ),
        ));

        self::assertNull($event1->tsProperty);
        self::assertNull($event2->tsProperty);
        self::assertInstanceOf(Param::class, $event1->propertyNode);
        self::assertInstanceOf(Property::class, $event2->propertyNode);
    }
}

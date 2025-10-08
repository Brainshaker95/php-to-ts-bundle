<?php

declare(strict_types=1);

namespace App\Tests;

use Brainshaker95\PhpToTsBundle\Command\DumpCommand;
use Brainshaker95\PhpToTsBundle\Serializer\Encoder\EncoderInterface;
use Brainshaker95\PhpToTsBundle\Serializer\Normalizer\NormalizerInterface;
use PHPat\Selector\Selector;
use PHPat\Test\Builder\TargetExcludeOrBuildStep;
use PHPat\Test\Builder\TipOrBuildStep;
use PHPat\Test\PHPat;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

final class ArchitectureTest
{
    public function testFinalClasses(): TipOrBuildStep
    {
        return PHPat::rule()
            ->classes(
                Selector::inNamespace('App\Tests'),
                Selector::inNamespace('Brainshaker95\PhpToTsBundle'),
            )
            ->excluding(
                Selector::inNamespace('Brainshaker95\PhpToTsBundle\Interface'),
                Selector::classname(DumpCommand::class),
                Selector::classname(EncoderInterface::class),
                Selector::classname(NormalizerInterface::class),
            )
            ->shouldBeFinal()
        ;
    }

    public function testAbstractClasses(): TipOrBuildStep
    {
        return PHPat::rule()
            ->classes(
                Selector::classname(DumpCommand::class),
            )
            ->shouldBeAbstract()
        ;
    }

    public function testInterfaces(): TipOrBuildStep
    {
        return PHPat::rule()
            ->classes(
                Selector::inNamespace('Brainshaker95\PhpToTsBundle\Interface'),
            )
            ->shouldBeInterface()
        ;
    }

    public function testSerializer(): TargetExcludeOrBuildStep
    {
        return PHPat::rule()
            ->classes(
                Selector::classname(EncoderInterface::class),
                Selector::classname(NormalizerInterface::class),
            )
            ->shouldApplyAttribute()
            ->classes(
                Selector::classname(AutoconfigureTag::class),
            )
        ;
    }

    public function testTools(): TargetExcludeOrBuildStep
    {
        return PHPat::rule()
            ->classes(
                Selector::inNamespace('App\Tests'),
                Selector::inNamespace('Brainshaker95\PhpToTsBundle'),
            )
            ->shouldNotConstruct()
            ->classes(
                Selector::inNamespace('Brainshaker95\PhpToTsBundle\Tool'),
            )
        ;
    }
}

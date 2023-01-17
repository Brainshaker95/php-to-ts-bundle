<?php

namespace Brainshaker95\PhpToTsBundle\Serializer;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;
use Traversable;

class Serializer extends SymfonySerializer
{
    /**
     * @param Traversable<NormalizerInterface> $normalizers
     * @param Traversable<EncoderInterface> $encoders
     */
    public function __construct(
        #[TaggedIterator('php_to_ts.serializer.normalizer')] Traversable $normalizers,
        #[TaggedIterator('php_to_ts.serializer.encoder')] Traversable $encoders,
    ) {
        parent::__construct(iterator_to_array($normalizers), iterator_to_array($encoders));
    }
}

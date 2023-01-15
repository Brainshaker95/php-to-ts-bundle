<?php

namespace Brainshaker95\PhpToTsBundle\Command;

use Brainshaker95\PhpToTsBundle\Interface\Config as C;
use Brainshaker95\PhpToTsBundle\Model\Config\PartialConfig;
use Brainshaker95\PhpToTsBundle\Service\Dumper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Contracts\Service\Attribute\Required;

abstract class DumpCommand extends Command
{
    private const INDENT_STYLE_KEY = C::INDENT_KEY . '_' . C::INDENT_STYLE_KEY;
    private const INDENT_COUNT_KEY = C::INDENT_KEY . '_' . C::INDENT_COUNT_KEY;

    #[Required]
    public Dumper $dumper;

    protected function configure(): void
    {
        $this
            ->addOption(
                name: C::OUTPUT_DIR_KEY,
                description: C::OUTPUT_DIR_DESC,
                default: C::OUTPUT_DIR_DEFAULT,
                mode: InputOption::VALUE_REQUIRED,
                shortcut: 'o',
            )
            ->addOption(
                name: C::FILE_TYPE_KEY,
                description: C::FILE_TYPE_DESC,
                default: C::FILE_TYPE_DEFAULT,
                mode: InputOption::VALUE_REQUIRED,
                shortcut: 't',
            )
            ->addOption(
                name: self::INDENT_STYLE_KEY,
                description: C::INDENT_STYLE_DESC,
                default: C::INDENT_STYLE_DEFAULT,
                mode: InputOption::VALUE_REQUIRED,
                shortcut: 'p',
            )
            ->addOption(
                name: self::INDENT_COUNT_KEY,
                description: C::INDENT_COUNT_DESC,
                default: C::INDENT_COUNT_DEFAULT,
                mode: InputOption::VALUE_REQUIRED,
                shortcut: 'c',
            )
            ->addOption(
                name: C::SORT_STRATEGIES_KEY,
                description: C::SORT_STRATEGIES_DESC,
                default: C::SORT_STRATEGIES_DEFAULT,
                mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                shortcut: 's',
            )
            ->addOption(
                name: C::FILE_NAME_STRATEGY_KEY,
                description: C::FILE_NAME_STRATEGY_DESC,
                default: C::FILE_NAME_STRATEGY_DEFAULT,
                mode: InputOption::VALUE_REQUIRED,
                shortcut: 'f',
            )
        ;
    }

    protected static function getConfig(InputInterface $input): C
    {
        return PartialConfig::fromArray([
            C::OUTPUT_DIR_KEY         => self::nonEmptyString($input, C::OUTPUT_DIR_KEY),
            C::FILE_TYPE_KEY          => self::nonEmptyString($input, C::FILE_TYPE_KEY),
            C::INDENT_KEY             => [
                C::INDENT_STYLE_KEY => self::nonEmptyString($input, self::INDENT_STYLE_KEY),
                C::INDENT_COUNT_KEY => self::nonNegativeInteger($input, self::INDENT_COUNT_KEY),
            ],
            C::SORT_STRATEGIES_KEY    => self::nonEmptyStringArray($input, C::SORT_STRATEGIES_KEY),
            C::FILE_NAME_STRATEGY_KEY => self::nonEmptyString($input, C::FILE_NAME_STRATEGY_KEY),
        ]);
    }

    protected static function nonEmptyString(InputInterface $input, string $key): string
    {
        $value = $input->getOption($key);

        if (!is_string($value) || !$value) {
            throw new InvalidOptionException(sprintf(
                'The "--%s" option has to be an non empty string.',
                $key,
            ));
        }

        return $value;
    }

    /**
     * @return int<0,max>
     */
    protected static function nonNegativeInteger(InputInterface $input, string $key): int
    {
        $value  = $input->getOption($key);
        $intval = intval($value);

        if (filter_var($value, FILTER_VALIDATE_INT) === false || $intval < 0) {
            throw new InvalidOptionException(sprintf(
                'The "--%s" option has to be a non negative integer.',
                $key,
            ));
        }

        return $intval;
    }

    /**
     * @return non-empty-string[]
     */
    protected static function nonEmptyStringArray(InputInterface $input, string $key): array
    {
        $value = $input->getOption($key);

        if (!is_array($value)
            || !empty(array_filter($value, fn (mixed $v) => !is_string($v) || (is_string($v) && !$v)))) {
            throw new InvalidOptionException(sprintf(
                'The "--%s" option has to be a non-empty-string array.',
                $key,
            ));
        }

        return $value;
    }
}

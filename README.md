<div id="top"></div>

[![Contributors][contributors-shield]][contributors-url]
[![Forks][forks-shield]][forks-url]
[![Stargazers][stars-shield]][stars-url]
[![Issues][issues-shield]][issues-url]
[![MIT License][license-shield]][license-url]

# PhpToTsBundle

Convert PHP model classes to TypeScript interfaces

> ⚠ **This project is still in a very early state** ⚠  
Everything is subject to change. Use at your own risk!

*[☄️ Bug reports / feature requests »][issues-url]*

<br>

## Table Of Contents

<!-- NOTICE: all anchors must not include the emoji to work on github, the ❤️ some reason must be url encoded though -->
* [👋 About The Project](#-about-the-project)
* [🚀 Installation](#-installation)
* [⚙ Configuration](#-configuration)
* [👀 Usage](#-usage)
* [💻 API](#-api)
  * [🤝 Events](#-events)
  * [💩 Dumper](#-dumper)
* [🔨 TODOs / Roadmap](#-todos--roadmap)
* [❤️ Contributing](#%EF%B8%8F-contributing)
* [⭐ License](#-license)
* [🌐 Acknowledgments](#-acknowledgments)

<p align="right"><a href="#top" title="Back to top">&nbsp;&nbsp;&nbsp;⬆&nbsp;&nbsp;&nbsp;</a></p>

## 👋 About The Project

This bundle aims to provide a simple way of working with strongly typed JSON response data.  
Maybe I should elaborate more on my future goals but for now this has to suffice.

<p align="right"><a href="#top" title="Back to top">&nbsp;&nbsp;&nbsp;⬆&nbsp;&nbsp;&nbsp;</a></p>

## 🚀 Installation

Currently this bundle is not available as a composer package. It can still be installed like this:

composer.json
```jsonc
{
  // ...
  "require": {
    // ...
    "brainshaker95/php-to-ts-bundle": "dev-master"
  },
  "repositories": [
    // ...
    {
      "type": "vcs",
      "url": "git@github.com:Brainshaker95/php-to-ts-bundle.git"
    }
  ]
}
```

```shell
composer update brainshaker95/php-to-ts-bundle
```

config/bundles.php
```php
<?php

return [
    // ...
    Brainshaker95\PhpToTsBundle\PhpToTsBundle::class => ['dev' => true],
];
```

<p align="right"><a href="#top" title="Back to top">&nbsp;&nbsp;&nbsp;⬆&nbsp;&nbsp;&nbsp;</a></p>

## ⚙ Configuration

packages\php_to_ts.yaml
```yaml
# Default configuration

php_to_ts:
  # Directory in which to look for models to include
  input_dir: src/Model/TypeScriptables
  
  # Directory in which to dump generated TypeScript interfaces
  output_dir: assets/ts/types/php-to-ts

  # File type to use for TypeScript interfaces
  file_type: !php/const Brainshaker95\PhpToTsBundle\Model\Config\FileType::TYPE_MODULE

  # Indentation used for generated TypeScript interfaces
  indent:
    # Indent style used for TypeScript interfaces
    style: !php/const Brainshaker95\PhpToTsBundle\Model\Config\Indent::STYLE_SPACE

    # Number of indent style characters per indent
    count: 2
  
  # Class names of sort strategies used for TypeScript properties
  sort_strategies: 
    - Brainshaker95\PhpToTsBundle\Model\Config\SortStrategy\AlphabeticalAsc
    - Brainshaker95\PhpToTsBundle\Model\Config\SortStrategy\ConstructorFirst
    - Brainshaker95\PhpToTsBundle\Model\Config\SortStrategy\ReadonlyFirst

  # Class name of file name strategies used for generated TypeScript files
  file_name_strategy: Brainshaker95\PhpToTsBundle\Model\Config\FileNameStrategy\KebabCase
```

<p align="right"><a href="#top" title="Back to top">&nbsp;&nbsp;&nbsp;⬆&nbsp;&nbsp;&nbsp;</a></p>

## 👀 Usage

This bundle exposes 3 different [commands](src/Command).  
All of them use the default configuration when no options are passed.  
Run `bin/console <COMMAND> -h` for a full list of available options.

Dumps all TypeScriptables in the given directory:
```shell
bin/console phptots:dump:dir [options]
```

Dumps all TypeScriptables in the given files and directories:
```shell
bin/console phptots:dump:files --input-files=path/to/file1 --input-files=path/to/file2 [options]
```

Dumps all TypeScriptables in the given file:
```shell
bin/console phptots:dump:file --input-file=path/to/file [options]
```

<p align="right"><a href="#top" title="Back to top">&nbsp;&nbsp;&nbsp;⬆&nbsp;&nbsp;&nbsp;</a></p>

## 💻 API

### 🤝 Events

Each time a TsInterface or TsProperty is generated during the dumping process an [event](src/Event) is dispatched.  
You can subscribe to these events if it is necessary to modify the output right before dumping.

Example implementation:
```php
<?php

namespace App\EventSubscriber;

use Brainshaker95\PhpToTsBundle\Event\TsInterfaceGeneratedEvent;
use Brainshaker95\PhpToTsBundle\Event\TsPropertyGeneratedEvent;
use Brainshaker95\PhpToTsBundle\Model\TsProperty;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TsInterfaceGeneratedSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            TsInterfaceGeneratedEvent::class => 'onGeneratedTsInterface',
            TsPropertyGeneratedEvent::class  => 'onGeneratedTsProperty',
        ];
    }

    public function onGeneratedTsInterface(TsInterfaceGeneratedEvent $event): void
    {
        $tsInterface = $event->tsInterface;

        // Filter out all constructor properties
        $tsInterface->properties = array_filter(
            $tsInterface->properties,
            fn (TsProperty $tsProperty) => !$tsProperty->isConstructorProperty,
        );
    }

    public function onGeneratedTsProperty(TsPropertyGeneratedEvent $event): void
    {
        // Hide all properties with `@phptots-hide` in their doc comment
        if (str_contains($event->propertyNode->getDocComment(), '@phptots-hide')) {
            $event->tsProperty = null;
        }
    }
}
```

<p align="right"><a href="#top" title="Back to top">&nbsp;&nbsp;&nbsp;⬆&nbsp;&nbsp;&nbsp;</a></p>

### 💩 Dumper

If you do want to implement your own way of initiating the dump process you can inject the [dumper service](src/Service/Dumper.php) into your own services.  
This of course can be done via all the various different ways of [dependency injection](https://symfony.com/doc/current/components/dependency_injection.html) and not only the one shown here:
```php
<?php

namespace App\Service;

use Brainshaker95\PhpToTsBundle\Model\Config\FileType;
use Brainshaker95\PhpToTsBundle\Model\Config\PartialConfig;
use Brainshaker95\PhpToTsBundle\Service\Dumper;
use Symfony\Contracts\Service\Attribute\Required;

class MyService
{
    #[Required]
    public Dumper $dumper;
    
    public function doTheThingsAndStuff(): void
    { 
        // See method descriptions for more detail
        $this->dumper->dumpDir();
        $this->dumper->dumpFiles(['path/to/file1', 'path/to/file2']);
        $this->dumper->dumpFile('path/to/file', new PartialConfig(fileType: FileType::TYPE_DECLARATION));
        $this->dumper->getTsInterfacesFromFile('path/to/file');
    }
}
```

<p align="right"><a href="#top" title="Back to top">&nbsp;&nbsp;&nbsp;⬆&nbsp;&nbsp;&nbsp;</a></p>

## 🔨 TODOs / Roadmap

- Generate type imports when TsInterface has parentName an TYPE_MODULE is chosen
- Document example TypeScriptable class
- Document TsController usage
- Support for @phpstan- and @psalm- prefixes in doc comments
- Generic types like shown here
  ```php
  /**
   * @template T
   * @param T $foo
   */
  public function __construct(
      public $foo,
  ) {
  }
  ```
  Should generate:
  ```ts
  interface MyType<T> {
    foo: T;
  }
  ```
  ___
  ```php
  /**
   * @template T of \Exception
   * @param T $foo
   */
  public function __construct(
      public \Exception $foo,
  ) {
  }
  ```
  Should generate:
  ```ts
  interface MyType<T extends Exception> {
    foo: T;
  }
  ```
  ___
  ```php
  /**
   * @template T of \Stringable
   * @param class-string<T> $foo
   */
  public function __construct(
      public string $foo,
      public \Stringable $bar,
  ) {
  }
  ```
  Should generate:
  ```ts
  interface MyType {
    foo: string;
    bar: Stringable;
  }
  ```
  ___
  ```php
  /**
   * @template T of \Stringable
   * @param class-string<T> $foo
   * @param T $baz
   */
  public function __construct(
      public string $foo,
      public \Stringable $bar,
      public $baz,
  ) {
  }
  ```
  Should generate:
  ```ts
  interface MyType<T extends Stringable> {
    foo: string;
    bar: Stringable;
    baz: T;
  }
  ```
  ___
  ```php
  /**
   * @template T of \Exception
   * @param array<string,T[]> $foo
   */
  public function __construct(
      public array $foo,
  ) {
  }
  ```
  Should generate:
  ```ts
  interface MyType<T extends Exception> {
    foo: Record<string,T[]>;
  }
  ```
- Complex iterable types like shown here
  ```php
  /**
   * @see {somewhere}
   * @var array{
   *     foo: string,
   * }
   * @since 1.1.0
   */
  public array $foo1;

  /**
   * @var array<string>
   * @since 1.1.0
   */
  public array $foo2;

  /**
   * @var array<int,string>
   */
  public array $foo3;

  /**
   * @var array<int,string[]>
   */
  public array $foo4;

  /**
   * @var array<int,array<int,string[]>>
   */
  public array $foo5;

  /**
   * @var string[]
   */
  public array $foo6;

  /**
   * @var string[][]
   */
  public array $foo7;

  /**
   * @var array<int,array{
   *     foo: string,
   *     bar: array{
   *         baz: array<string,int[]>,
   *     },
   * }>
   */
  public array $foo8;

  /**
   * @var iterable<Foo>
   */
  public iterable $foo10;

  /**
   * @var iterable<Foo[]>
   */
  public iterable $foo11;

  /**
   * @var iterable<array<int,Foo|(Bar&Baz)[]>>
   */
  public iterable $foo12;

  /**
   * @var iterable<array<string,Foo|(Bar&Baz)[]>>
   */
  public iterable $foo13;

  /**
   * @param Foo[] $foo11
   * @param array{
   *   foo: string,
   * } $foo12
   * @param array<Foo[][]> $foo13
   */
  public function __construct(
      public array $foo12,
      public array $foo13,
      private array $foo14,
      string $foo15,
  ) {
  }
  ```
  Should generate:
  ```ts
  interface MyType {
    foo1: {
      foo: string;
    };
    foo2: string[];
    foo3: string[];
    foo4: string[][];
    foo5: string[][][];
    foo6: string[];
    foo7: string[][];
    foo8: {
      foo: string;
      bar: {
        baz: Record<string, number[]>;
      };
    }[];
    foo10: Foo[];
    foo11: Foo[][];
    foo12: (Foo|(Bar&Baz))[][][];
    foo13: Record<string, (Foo|(Bar&Baz))[]>[]
  }
  ```

<p align="right"><a href="#top" title="Back to top">&nbsp;&nbsp;&nbsp;⬆&nbsp;&nbsp;&nbsp;</a></p>

## ❤️ Contributing

Contributions are what make the open source community such an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**.

If you have a suggestion that would make this better, please fork the repo and create a pull request. You can also simply open an issue with the tag "enhancement".
Don't forget to give the project a star! Thanks again!

1. Fork the Project
2. Create your Feature Branch => `git checkout -b feature/my-new-feature`
3. Commit your Changes => `git commit -m 'feat(my-new-feature): adds some awesome new feature'`
4. Push to the Branch => `git push origin feature/my-new-feature`
5. Open a Pull Request

<p align="right"><a href="#top" title="Back to top">&nbsp;&nbsp;&nbsp;⬆&nbsp;&nbsp;&nbsp;</a></p>

## ⭐ License

Distributed under the MIT License. See [LICENSE](./LICENSE) for more information.

<p align="right"><a href="#top" title="Back to top">&nbsp;&nbsp;&nbsp;⬆&nbsp;&nbsp;&nbsp;</a></p>

## 🌐 Acknowledgments

* [PHP](https://www.php.net)
* [TypeScript](https://www.typescriptlang.org)
* [Symfony](https://symfony.com)
* [PHP Parser](https://github.com/nikic/PHP-Parser)
* [PHPStan](https://github.com/phpstan/phpstan)
* [PHP Coding Standards Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer)
* [php-to-typescript-converter](https://github.com/snakedove/php-to-typescript-converter) by [snakedove](https://github.com/snakedove)
* [Best-README-Template](https://github.com/othneildrew/Best-README-Template) by [othneildrew](https://github.com/othneildrew)
* [Choose an Open Source License](https://choosealicense.com)
* [Img Shields](https://shields.io)

<p align="right"><a href="#top" title="Back to top">&nbsp;&nbsp;&nbsp;⬆&nbsp;&nbsp;&nbsp;</a></p>

<!-- END OF CONTENT -->

<!-- MARKDOWN LINKS & IMAGES -->
<!-- https://www.markdownguide.org/basic-syntax/#reference-style-links -->

[contributors-url]: https://github.com/Brainshaker95/php-to-ts-bundle/graphs/contributors
[contributors-shield]: https://img.shields.io/github/contributors/Brainshaker95/php-to-ts-bundle.svg?style=flat-square

[forks-url]: https://github.com/Brainshaker95/php-to-ts-bundle/network/members
[forks-shield]: https://img.shields.io/github/forks/Brainshaker95/php-to-ts-bundle.svg?style=flat-square

[stars-url]: https://github.com/Brainshaker95/php-to-ts-bundle/stargazers
[stars-shield]: https://img.shields.io/github/stars/Brainshaker95/php-to-ts-bundle.svg?style=flat-square

[issues-url]: https://github.com/Brainshaker95/php-to-ts-bundle/issues
[issues-shield]: https://img.shields.io/github/issues/Brainshaker95/php-to-ts-bundle.svg?style=flat-square

[license-url]: https://github.com/Brainshaker95/php-to-ts-bundle/blob/master/LICENSE
[license-shield]: https://img.shields.io/github/license/Brainshaker95/php-to-ts-bundle.svg?style=flat-square

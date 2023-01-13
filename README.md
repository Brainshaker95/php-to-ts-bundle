<div id="top"></div>

[![Contributors][contributors-shield]][contributors-url]
[![Forks][forks-shield]][forks-url]
[![Stargazers][stars-shield]][stars-url]
[![Issues][issues-shield]][issues-url]
[![MIT License][license-shield]][license-url]

# PhpToTsBundle

Converts PHP model classes to TypeScript interfaces

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
  output_dir: resources/ts/types/generated

  # File type to use for TypeScript interfaces
  file_type: !php/const Brainshaker95\PhpToTsBundle\Model\FileType::TYPE_MODULE

  # Indentation used for generated TypeScript interfaces
  indent:
    # Indent style used for TypeScript interfaces
    style: !php/const Brainshaker95\PhpToTsBundle\Model\Indent::STYLE_SPACE

    # Number of indent style characters per indent
    count: 2
  
  # Class names of sort strategies used for TypeScript properties
  sort_strategies: 
    - Brainshaker95\PhpToTsBundle\SortStrategy\AlphabeticalAsc
    - Brainshaker95\PhpToTsBundle\SortStrategy\ConstructorFirst
    - Brainshaker95\PhpToTsBundle\SortStrategy\ReadonlyFirst

  # Class name of file name strategies used for generated TypeScript files
  file_name_strategy: Brainshaker95\PhpToTsBundle\FileNameStrategy\KebabCase
```

<p align="right"><a href="#top" title="Back to top">&nbsp;&nbsp;&nbsp;⬆&nbsp;&nbsp;&nbsp;</a></p>

## 👀 Usage

TODO 🙃

<p align="right"><a href="#top" title="Back to top">&nbsp;&nbsp;&nbsp;⬆&nbsp;&nbsp;&nbsp;</a></p>

## 💻 API

TODO 🙃

<p align="right"><a href="#top" title="Back to top">&nbsp;&nbsp;&nbsp;⬆&nbsp;&nbsp;&nbsp;</a></p>

## 🔨 TODOs / Roadmap

- Events for modifying used TsInterface and TsProperty instances
- Command variations (directory, single file, multiple files)
- Support for @phpstan- and @psalm- prefixes in doc comments
- FileNameStrategies for generated interface ts files (kebab, camel, snake, pascal)
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

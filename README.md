# File System Storage
A file system store engine written in PHP

> [!WARNING]
> **Experimental project – feedback is appreciated**

## Why

Mostly for fun, exploration of the PHP language and learning, breaking old habits, trying controversial ideas, and experimenting with new ones.

## Features

- [x] `BlobStorage` - A file system store for blobs of data
- [x] `KeyValueStorage` - A file system store for key-value pairs based on `BlobStorage` under the hood
- [x] `CollectionsStorage` - A file system store for collections of data, based on `KeyValueStorage` under the hood

### Implementations:

- [x] `File` – Handles file operations
- [x] `Directory` – Handles directory operations
- [x] `JSONSerializer` - JSON implementation of `Serializer`

### Interfaces:

- [x] `Serializer` - Interface for serializing and deserializing data

## Installation

> [!IMPORTANT]
> This project is not yet published to Packagist. You need to add the repository manually or clone the repository as a submodule.

### Option 1: Add as a Git submodule

```shell
$ git submodule add git@github.com:attitude/file-system-storage-php.git path/to/file-system-storage-php
```

### Option 2: Add as a dependency using Composer

Update `composer.json` of your project:

```json
{
    ...,
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/attitude/file-system-storage-php.git"
        }
    ],
    "require": {
        "attitude/file-system-storage": "dev-main"
    }
}
```

```shell
$ composer install
```

### Option 3: Download the repository as a ZIP

---

## Usage

### KeyValueStorage

```php
use Attitude\FileSystemStorage\KeyValueStorage;

$storage = new KeyValueStorage('path/to/storage', 'namespace', new JSONSerializer());

$storage->set('key', 'value');
$storage->get('key'); // value
$storage->delete('key');
```

### CollectionsStorage

`CollectionsStorage` is a wrapper around `KeyValueStorage` that provides a way to store collections of data in a structured way. It uses a pattern to define the directory structure and file name for each item in the collection.

The pattern is a tuple of a string and a callable function.

- The string is a pattern for the directory structure and file name.
- The callable function is used to parse the item and return a tuple of identifiers and the item itself, which can be altered here before saving.

```php
<?php declare(strict_types=1);

use Attitude\FileSystemStorage\CollectionsStorage;

$identifiers = [
  // The pattern for the directory structure and file name
  '{year}/{month}/{day}/{slug}-{id}',
  // The callable function to parse the item, enrich it with identifiers and return a tuple of identifiers and the item
  function(object $item): object {
    $item->id = $item->id ?? uniqid();
    $item->slug = $item->slug ?? null;

    if (!$item->slug) {
      throw new \InvalidArgumentException('Item must have a slug');
    }

    $date = new \DateTimeImmutable($item->createdAt ?? date('Y-m-d H:i:s'));

    $item->id' => $item->id;
    $item->year' => $date->format('Y');
    $item->month' => $date->format('m');
    $item->day' => $date->format('d');
    $item->slug' => $item->slug;

    return $item;
  }
];

$storage = new KeyValueStorage('path/to/storage', new JSONSerializer());
$collections = new CollectionsStorage('posts', $identifiers, $storage);

$article = [
    'title' => 'Hello, World!',
    'slug' => 'hello-world',
    'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
    'createdAt' => '2024-04-14 07:42:01',
];

$storedPath  = $collections->store($article);
$collections->delete($storedPath);
```

---

_Enjoy!_

Created by [martin_adamko](https://www.threads.net/@martin_adamko)

<?php declare(strict_types=1);

use Attitude\CLILogger\Logger;
use Attitude\FileSystemStorage\Blob\Storage as BlobStorage;
use Attitude\FileSystemStorage\Collection\Storage as CollectionStorage;
use Attitude\FileSystemStorage\Serializer\JSONSerializer;
use Attitude\FileSystemStorage\KeyValue\Storage as KeyValueStorage;

const STORAGE_DIR = '.storage';

function today(): \DateTimeImmutable {
  static $today = new \DateTimeImmutable();
  return $today;
}

function useBlobStorage(string $path, bool $withLogger = false) {
  static $logger = new Logger();

  $storage = new BlobStorage(
    STORAGE_DIR.DIRECTORY_SEPARATOR.$path,
  );

  if ($withLogger) {
    $storage->setLogger($logger);
  }

  return $storage;
}

function useKeyValueStorage(string $namespace, bool $withLogger = false) {
  static $logger = new Logger();

  $storage = new KeyValueStorage(
    STORAGE_DIR,
    $namespace,
    new JSONSerializer(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
  );

  if ($withLogger) {
    $storage->setLogger($logger);
  }

  return $storage;
}

function useCollectionsStorage(string $namespace, bool $withLogger = false) {
  static $logger = new Logger();
  static $storage = useKeyValueStorage($namespace, $withLogger);

  $collectionsStorage = new CollectionStorage(
    ['{year}/{month}/{day}-{id}', function (object $item) {
      $now = today();
      $item = (object) $item;

      if (!isset($item->id)) {
        throw new \Exception('Item must have an id');
      }

      $item->year = $item->year ?? $now->format('Y');
      $item->month = $item->month ?? $now->format('m');
      $item->day = $item->day ?? $now->format('d');

      return $item;
    }],
    $storage,
  );

  if ($withLogger) {
    $storage->setLogger($logger);
    $collectionsStorage->setLogger($logger);
  }

  return $collectionsStorage;
}

uses()->afterAll(function() {
  useKeyValueStorage('.')->purge();
})->in(dirname(__DIR__));

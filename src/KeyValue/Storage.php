<?php declare(strict_types=1);

namespace Attitude\FileSystemStorage\KeyValue;

use Attitude\FileSystemStorage\Blob\File;
use Attitude\FileSystemStorage\Blob\Storage as BlobStorage;
use Attitude\FileSystemStorage\Serializer\Serializer;
use Psr\Log\LoggerAwareTrait;

/**
 * Represents a key-value storage implementation.
 *
 * This class provides methods to store, retrieve, delete, and check the existence of key-value pairs.
 * It uses a file system storage backend to persist the data.
 */
final class Storage {
  use LoggerAwareTrait;

  /**
   * The directory path where the key-value storage is located.
   *
   * @var string
   */
  protected string $dir;

  /**
   * @var string $namespace The namespace for the storage.
   */
  protected string $namespace;

  /**
   * @var BlobStorage $storage The blob storage used for key-value storage.
   */
  protected BlobStorage $storage;

  /**
   * Constructs a new Storage instance.
   *
   * @param string $dir The directory path where the storage files are stored.
   * @param string $namespace The namespace for the storage files.
   * @param Serializer $serializer The serializer used to serialize and deserialize the data.
   */
  public function __construct(
    string $dir,
    string $namespace,
    protected Serializer $serializer,
  ) {
    $this->namespace = trim($namespace, '/\\');
    $this->dir = rtrim($dir, '/\\');

    $path = $this->namespace === '.'
      ? $this->dir
      : $this->dir.DIRECTORY_SEPARATOR.$this->namespace;

    $this->storage = new BlobStorage($path);
  }

  public function __get(string $name): mixed {
    return match ($name) {
      'dir' => $this->dir,
      'namespace' => $this->namespace,
      'serializer' => $this->serializer,
      'storage' => $this->storage,
    };
  }

  /**
   * Returns the File object associated with the given key.
   *
   * @param string $key The key to retrieve the File object for.
   * @return File The File object associated with the given key.
   */
  protected function fileForKey(string $key): File {
    $file = $this->storage->file("{$key}.{$this->serializer->getExtension()}", $this->serializer);

    if ($this->logger) {
      $file->setLogger($this->logger); // @codeCoverageIgnore
    }

    return $file;
  }

  /**
   * Retrieves the value associated with the given key.
   *
   * @param string $key The key to retrieve the value for.
   * @return mixed The value associated with the given key.
   */
  public function get(string $key): mixed {
    return $this->fileForKey($key)->read();
  }

  /**
   * Sets the value for a given key in the storage.
   *
   * @param string $key The key to set the value for.
   * @param mixed $value The value to set.
   * @return void
   */
  public function set(string $key, mixed $value): void {
    $this->fileForKey($key)->write($value);
  }

  /**
   * Deletes the value for a given key from the storage.
   *
   * @param string $key The key to delete the value for.
   * @return void
   */
  public function delete(string $key): void {
    $file = $this->fileForKey($key);
    $deleted = $file->delete();
    $this->storage->clearParents($deleted);
  }

  /**
   * Checks if a given key exists in the storage.
   *
   * @param string $key The key to check.
   * @return bool Returns true if the key exists, false otherwise.
   */
  public function has(string $key): bool {
    return $this->fileForKey($key)->exists();
  }

  /**
   * Returns an array of all the keys in the storage.
   *
   * @return array An array of keys.
   */
  public function keys(): array {
    $extension = $this->serializer->getExtension();

    return array_map(fn(string $file) => substr($file, 0, -1 * (strlen($extension) + 1)), $this->storage->list(fn(string $file) => str_ends_with($file, $extension)));
  }

  /**
   * Purges the storage, deleting all keys and values.
   *
   * @return void
   */
  public function purge(): void {
    $this->storage->purge();
  }
}

<?php declare(strict_types=1);

namespace Attitude\FileSystemStorage\Collection;

use Attitude\FileSystemStorage\KeyValue\Storage as KeyValueStorage;
use Psr\Log\LoggerAwareTrait;

/**
 * Represents a storage collection that provides methods for storing, retrieving, and deleting data.
 */
final class Storage {
  use LoggerAwareTrait;

  /**
   * @var Identifier $identifiers The collection of identifiers.
   */
  protected Identifier $identifiers;

  /**
   * Storage constructor.
   *
   * @param array $identifiers An array of identifiers.
   * @param KeyValueStorage $storage The key-value storage implementation.
   */
  public function __construct(
    array $identifiers,
    protected KeyValueStorage $storage,
  ) {
    if (array_is_list($identifiers)) {
      $this->identifiers = new Identifier(...$identifiers);
    } else {
      throw new \InvalidArgumentException('The identifiers must be an array.');
    }
  }

  /**
   * Magic method to get the value of a property.
   *
   * @param string $name The name of the property.
   * @return mixed The value of the property.
   */
  public function __get(string $name): mixed {
    return match ($name) {
      'identifiers' => $this->identifiers,
      'storage' => $this->storage,
    };
  }

  /**
   * Converts the identifier to an Entry object.
   *
   * @param string|array|object $identifier The identifier to convert.
   * @return Entry The converted Entry object.
   */
  protected function identifiersKeyValue(string|array|object $identifier): Entry {
    if (is_array($identifier) || is_object($identifier)) {
      return $this->identifiers->parse((object) $identifier);
    } else {
      return new Entry($identifier, null);
    }
  }

  /**
   * Retrieves the value associated with the given identifier.
   *
   * @param string|array|object $identifier The identifier to retrieve the value for.
   * @return mixed The value associated with the identifier.
   */
  public function get(string|array|object $identifier): mixed {
    return $this->storage->get($this->identifiersKeyValue($identifier)[0]);
  }

  /**
   * Stores the given value and returns the associated key.
   *
   * @param array|object $value The value to store.
   * @return string The key associated with the stored value.
   */
  public function store(array|object $value): string {
    [$key, $value] = $this->identifiersKeyValue($value);
    $this->storage->set($key, $value);

    return $key;
  }

  /**
   * Deletes the value associated with the given identifier.
   *
   * @param string|array|object $identifier The identifier to delete the value for.
   * @return void
   */
  public function delete(string|array|object $identifier): void {
    $this->storage->delete($this->identifiersKeyValue($identifier)[0]);
  }

  /**
   * Checks if a value is associated with the given identifier.
   *
   * @param string|array|object $identifier The identifier to check.
   * @return bool True if a value is associated with the identifier, false otherwise.
   */
  public function has(string|array|object $identifier): bool {
    return $this->storage->has($this->identifiersKeyValue($identifier)[0]);
  }

  /**
   * Retrieves all keys in the storage.
   *
   * @return array An array of all keys in the storage.
   */
  public function all(): array {
    return $this->storage->keys();
  }

  /**
   * Deletes all values in the storage.
   *
   * @return void
   */
  public function purge(): void {
    $this->storage->purge();
  }
}

<?php declare(strict_types=1);

namespace Attitude\FileSystemStorage\Collection;

use Attitude\FileSystemStorage\Exception;

/**
 * Represents a key value entry in a collection.
 */
class Entry extends \ArrayIterator {
  /**
   * Constructs a new Entry instance.
   *
   * @param string $key The key of the entry.
   * @param mixed $value The value of the entry.
   */
  public function __construct(string $key, mixed $value) {
    parent::__construct([$key, $value]);
  }

  /**
   * Magic getter method to access the key and value of the entry.
   *
   * @param string $name The name of the property to get.
   * @return mixed The value of the property.
   */
  public function __get(string $name): mixed {
    return match($name) {
      'key' => $this[0],
      'value' => $this[1],
    };
  }

  /**
   * Magic setter method to prevent modifying the entry.
   *
   * @param mixed $name The name of the property to set.
   * @param mixed $value The value to set.
   * @throws Exception When attempting to modify the entry.
   */
  public function __set($name, $value) {
    throw new Exception('Entry tuple is immutable');
  }
}

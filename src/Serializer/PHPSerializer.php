<?php declare(strict_types=1);

namespace Attitude\FileSystemStorage\Serializer;

use Attitude\FileSystemStorage\Exception;
/**
 * The PHPSerializer class implements the Serializer interface and provides
 * serialization and deserialization functionality using PHP's built-in
 * serialize() and unserialize() functions.
 */
final class PHPSerializer implements Serializer {
  /**
   * The flags to be used during serialization.
   *
   * @var int
   */
  public function __construct(private int $flags = 0) {}

  /**
   * Serializes a value into a string.
   *
   * @param mixed $value The value to be serialized.
   * @return string The serialized value.
   * @throws Exception If the serialization fails.
   */
  public function serialize(mixed $value): string {
    $serialized = serialize($value);

    if ($serialized === false) {
      throw new Exception('Failed to serialize value', 500); // @codeCoverageIgnore
    } else {
      return $serialized;
    }
  }

  /**
   * Deserializes a string into a value.
   *
   * @param string $value The serialized value.
   * @return mixed The deserialized value.
   */
  public function deserialize(string $value): mixed {
    return unserialize($value, ['allowed_classes' => false]);
  }

  /**
   * Gets the file extension associated with the serialized format.
   *
   * @return string The file extension.
   */
  public function getExtension(): string {
    return 'php.txt';
  }
}

<?php declare(strict_types=1);

namespace Attitude\FileSystemStorage\Serializer;

use Attitude\FileSystemStorage\Exception;

/**
 * JSONSerializer class implements the Serializer interface and provides methods for serializing and deserializing data using JSON format.
 */
final class JSONSerializer implements Serializer {
  /**
   * Constructs a new JSONSerializer instance.
   *
   * @param int $flags The JSON encoding options.
   */
  public function __construct(private int $flags = 0) {}

  /**
   * Serializes a value into a JSON string.
   *
   * @param mixed $value The value to be serialized.
   * @return string The serialized JSON string.
   * @throws Exception If the serialization fails.
   */
  public function serialize(mixed $value): string {
    $serialized = json_encode($value, $this->flags);

    if ($serialized === false) {
      throw new Exception('Failed to serialize value', 500); // @codeCoverageIgnore
    } else {
      return $serialized;
    }
  }

  /**
   * Deserializes a JSON string into a PHP value.
   *
   * @param string $value The JSON string to be deserialized.
   * @return mixed The deserialized PHP value.
   */
  public function deserialize(string $value): mixed {
    return json_decode($value, true);
  }

  /**
   * Gets the file extension associated with the serialized format.
   *
   * @return string The file extension.
   */
  public function getExtension(): string {
    return 'json';
  }
}

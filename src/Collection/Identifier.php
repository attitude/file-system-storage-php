<?php declare(strict_types=1);

namespace Attitude\FileSystemStorage\Collection;

require_once '_Entry.php';

/**
 * Represents an identifier for a collection.
 */
final class Identifier {
  /**
   * @var string The pattern used for the identifier.
   */
  protected string $pattern;

  /**
   * @var array The placeholders used in the pattern.
   */
  protected array $placeholders;

  /**
   * @var array The keys used in the pattern.
   */
  protected array $keys;

  /**
   * Identifier constructor.
   *
   * @param string $pattern The pattern for the identifier.
   * @param \Closure $parser The parser function for the identifier.
   * @throws \InvalidArgumentException When the identifiers pattern expects at least one `{placeholder}`.
   */
  public function __construct(string $pattern, protected \Closure $parser) {
    $this->pattern = trim(trim($pattern), DIRECTORY_SEPARATOR);

    $pattern = explode('/', trim($this->pattern));
    $pattern = array_reduce(
      array_filter(array_map([$this, 'getPlaceholderKeys'], $pattern)),
      'array_merge',
      []
    );

    if (empty($pattern)) {
      throw new \InvalidArgumentException('Identifiers pattern expects at least one `{placeholder}`', 500);
    }

    $this->placeholders = array_keys($pattern);
    $this->keys = array_values($pattern);
  }

  /**
   * Magic method to get the value of a property dynamically.
   *
   * @param string $name The name of the property to get.
   * @return mixed The value of the property.
   */
  public function __get(string $name): mixed {
    return match ($name) {
      'pattern' => $this->pattern,
      'placeholders' => $this->placeholders,
      'keys' => $this->keys,
    };
  }

  /**
   * Retrieves the keys of the placeholders in the given pattern.
   *
   * @param string $pattern The pattern containing placeholders.
   * @return array|null An array of placeholder keys if placeholders are found, null otherwise.
   */
  protected function getPlaceholderKeys(string $pattern): array|null {
    $placeholders = [];

    if (preg_match_all('/\{([^\}]+)\}/', $pattern, $placeholders)) {
      return array_combine($placeholders[0], $placeholders[1]);
    } else {
      return null;
    }
  }

  /**
   * Parses an array or object and returns an Entry object.
   *
   * @param array|object $item The array or object to parse.
   * @return Entry The parsed Entry object.
   */
  public function parse(array|object $item): Entry {
    $arguments = (object) ($this->parser)($item);
    $values = array_map(fn($key) => $arguments->{$key}, $this->keys);
    $identifier = str_replace($this->placeholders, $values, $this->pattern);

    return new Entry($identifier, $arguments);
  }
}

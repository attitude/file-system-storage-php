<?php declare(strict_types=1);

namespace Attitude\FileSystemStorage\Blob;

use Attitude\FileSystemStorage\Blob\Directory;
use Attitude\FileSystemStorage\Exception;
use Attitude\FileSystemStorage\Serializer\Serializer;
use Psr\Log\LoggerAwareTrait;

/**
 * Represents a file in the file system.
 *
 * This class provides methods to interact with files, such as reading and writing data.
 */
final class File {
  use LoggerAwareTrait;

  /**
   * Creates a new File instance.
   *
   * @param string $path The path of the file.
   * @param Serializer|null $serializer The serializer to use for serializing and deserializing data (optional).
   */
  public function __construct(private string $path, private Serializer|null $serializer = null) {
  }

  public function __get(string $name): mixed {
    return match ($name) {
      'path' => $this->path,
      'serializer' => $this->serializer,
    };
  }

  /**
   * Checks if the file exists.
   *
   * @return bool Returns true if the file exists, false otherwise.
   * @throws Exception If the path is not a file.
   */
  public function exists(): bool {
    if (file_exists($this->path)) {
      if (is_file($this->path)) {
        $this->logger?->info("File '{$this->path}' exists");

        return true;
      } else {
        throw new Exception("Path '{$this->path}' is not a file", 400);
      }
    } else {
      return false;
    }
  }

  /**
   * Creates a file at the specified path.
   *
   * If the file already exists, it checks if it is a regular file. If it is, it logs a message and returns the current instance.
   * If it is not a regular file, it throws an exception.
   *
   * If the file does not exist, it creates the necessary directory structure using the parent directory of the file path.
   * It then creates an empty file at the specified path and writes an empty string to it.
   * If any error occurs during the file creation or writing, it throws an exception.
   *
   * @return self The current instance of the File class.
   * @throws Exception If the file already exists and is not a regular file, or if any error occurs during file creation or writing.
   */
  public function create(): self {
    if (file_exists($this->path)) {
      if (is_file($this->path)) {
        $this->logger?->info("File '{$this->path}' already exists");

        return $this;
      } else {
        throw new Exception("Path '{$this->path}' is not a file", 400);
      }
    } else {
      (new Directory(dirname($this->path)))->create();

      $resource = fopen($this->path, 'w');

      if ($resource === false) {
        throw new Exception("Failed to open file '{$this->path}'", 500); // @codeCoverageIgnore
      }

      flock($resource, LOCK_EX);
      $bytesWritten = fwrite($resource, '');
      flock($resource, LOCK_UN);
      fclose($resource);

      if ($bytesWritten === false) {
        throw new Exception("Failed to write to '{$this->path}'", 500); // @codeCoverageIgnore
      } else {
        $this->logger?->info("Created file '{$this->path}'");

        return $this;
      }
    }
  }

  /**
   * Updates the access time and modification time of the file.
   *
   * @param int|null $mtime The modification time to set for the file. If not provided, the current time is used.
   * @param int|null $atime The access time to set for the file. If not provided, the current time is used.
   * @return self Returns the updated File object.
   * @throws Exception If the file does not exist or if the touch operation fails.
   */
  public function touch(int $mtime = null, int $atime = null): self {
    if (!$this->exists()) {
      $this->create();
    }

    if (touch($this->path, $mtime, $atime) === false) {
      throw new Exception("Failed to touch '{$this->path}'", 500); // @codeCoverageIgnore
    } else {
      $this->logger?->info("Touched '{$this->path}'");

      return $this;
    }
  }

  /**
   * Reads the contents of the file.
   *
   * @return mixed The contents of the file.
   *
   * @throws Exception If the file does not exist or if there is an error reading the file.
   */
  public function read(): mixed {
    if ($this->exists()) {
      $resource = fopen($this->path, 'r');

      if ($resource === false) {
        throw new Exception("Failed to open file '{$this->path}'", 500); // @codeCoverageIgnore
      }

      flock($resource, LOCK_SH);
      $value = stream_get_contents($resource);
      flock($resource, LOCK_UN);
      fclose($resource);

      if ($value === false) {
        throw new Exception("Failed to read file '{$this->path}'", 500); // @codeCoverageIgnore
      } else {
        $this->logger?->info("Read from '{$this->path}'", ['value' => $value]);

        return $this->serializer ? $this->serializer->deserialize($value) : $value;
      }
    } else {
      throw new Exception("File '{$this->path}' does not exist", 404);
    }
  }

  /**
   * Writes a value to the file.
   *
   * If the file does not exist, it will be created.
   *
   * @param mixed $value The value to write to the file.
   * @return self Returns the current instance of the File object.
   * @throws Exception If there is an error opening or writing to the file.
   */
  public function write(mixed $value): self {
    if (!$this->exists()) {
      $this->create();
    }

    $resource = fopen($this->path, 'w');

    if ($resource === false) {
      throw new Exception("Failed to open file '{$this->path}'", 500); // @codeCoverageIgnore
    }

    flock($resource, LOCK_EX);
    $bytesWritten = fwrite($resource, $this->serializer ? $this->serializer->serialize($value) : $value);
    flock($resource, LOCK_UN);
    fclose($resource);

    if ($bytesWritten === false) {
      throw new Exception("Failed to write to '{$this->path}'", 500); // @codeCoverageIgnore
    } else {
      $this->logger?->info("Wrote to '{$this->path}'", ['value' => $value]);

      return $this;
    }
  }

  /**
   * Deletes the file.
   *
   * @return self
   * @throws Exception If the file deletion fails.
   */
  public function delete(): self {
    if (file_exists($this->path)) {
      if (unlink($this->path) === false) {
        throw new Exception("Failed to delete '{$this->path}'", 500); // @codeCoverageIgnore
      } else {
        $this->logger?->info("Deleted '{$this->path}'");
      }
    } else {
      $this->logger?->warning("File '{$this->path}' is already deleted");
    }

    return $this;
  }
}

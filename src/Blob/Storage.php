<?php declare(strict_types=1);

namespace Attitude\FileSystemStorage\Blob;

use Attitude\FileSystemStorage\Exception;
use Attitude\FileSystemStorage\Serializer\Serializer;
use Psr\Log\LoggerAwareTrait;

/**
 * Represents a storage for managing files and directories.
 *
 * This class provides methods to interact with files and directories, such as creating, listing, and purging them.
 */
final class Storage {
  use LoggerAwareTrait;

  protected Directory $directory;

  /**
   * Constructs a new Storage instance.
   *
   * @param string $path The path to the storage directory.
   */
  public function __construct(public readonly string $path) {
    $this->directory = (new Directory($path))->create();
  }

  public function __get(string $name): mixed {
    return match ($name) {
      'path' => $this->path,
      'directory' => $this->directory,
    };
  }

  /**
   * Creates a new File object with the specified filename and serializer.
   *
   * @param string $filename The name of the file.
   * @param Serializer $serializer The serializer to be used for the file.
   * @return File The newly created File object.
   */
  public function file(string $filename, Serializer $serializer): File {
    $file = new File($this->path.DIRECTORY_SEPARATOR.$filename, $serializer);

    if ($this->logger) {
      $file->setLogger($this->logger); // @codeCoverageIgnore
    }

    return $file;
  }

  /**
   * Returns a list of items in the storage.
   *
   * @param callable|null $filter An optional filter function to apply to the items.
   * @return array The list of items in the storage.
   */
  public function list(callable $filter = null) {
    return $this->directory->list($filter);
  }

  /**
   * Purges the storage by deleting all files and directories.
   */
  public function purge(): void {
    $this->logger?->info("Purging storage '{$this->path}'"); // @codeCoverageIgnore
    $this->directory->delete();
  }

  /**
   * An array of file and directory names that are safe to ignore.
   * These names include common system files and directories that do not need to be processed.
   *
   * @var array
   */
  const SAFE_TO_IGNORE = ['.', '..', '.DS_Store', '.Trashes', 'Thumbs.db', 'desktop.ini'];

  /**
   * Clears the parent directories of a file.
   *
   * @param File $file The file to clear its parent directories.
   * @return bool Returns true if all parent directories were successfully cleared, false otherwise.
   * @throws Exception If the file still exists or if the parent path is not within the storage path.
   */
  public function clearParents(File $file): bool {
    if ($file->exists()) {
      throw new Exception("File '{$file->path}' still exists", 400);
    } else {
      $parents = dirname($file->path);

      if (strpos($parents, $this->path) !== 0) {
        throw new Exception("Path '{$parents}' is not in storage '{$this->path}'", 400);
      }

      $parents = substr($parents, strlen($this->path) + 1);
      $parents = explode(DIRECTORY_SEPARATOR, $parents);

      $this->logger?->info('Parents:', $parents);

      while (count($parents) >= 1) {
        $path = $this->path.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $parents);
        $this->logger?->info("Clearing directory '{$path}'");

        $pathDirectory = new Directory($path);

        if ($pathDirectory->exists()) {
          if (empty($pathDirectory->list(fn($file) => !in_array($file, self::SAFE_TO_IGNORE)))) {
            $this->logger?->info("Deleting empty directory '{$path}'");

            $pathDirectory->delete();
          } else {
            $this->logger?->warning("Directory '{$path}' is not empty");

            return false;
          }
        } else {
          $this->logger?->info("Directory '{$path}' does not exist");

          return false;
        }

        array_pop($parents);
      }

      return true;
    }
  }
}

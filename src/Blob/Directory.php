<?php declare(strict_types=1);

namespace Attitude\FileSystemStorage\Blob;

use Attitude\FileSystemStorage\Exception;
use Psr\Log\LoggerAwareTrait;

final class Directory {
  use LoggerAwareTrait;

  /**
   * Represents a directory in the file system.
   *
   * @var string $path The path of the directory.
   */
  private readonly string $path;

  /**
   * Constructs a new Directory object.
   *
   * @param string $path The path of the directory.
   */
  public function __construct(string $path) {
    $this->path = rtrim($path, '/\\');
  }

  public function __get(string $name): mixed {
    return match ($name) {
      'path' => $this->path,
    };
  }

  /**
   * Checks if the directory exists.
   *
   * @return bool Returns true if the directory exists, false otherwise.
   * @throws Exception If the path is not a directory.
   */
  public function exists(): bool {
    if (file_exists($this->path)) {
      if (is_dir($this->path)) {
        $this->logger?->info("Directory '{$this->path}' exists");

        return true;
      } else {
        throw new Exception("Path '{$this->path}' is not a directory", 400);
      }
    } else {
      return false;
    }
  }

  /**
   * Creates a directory at the specified path.
   *
   * @return self Returns the current instance of the Directory class.
   * @throws Exception If the path already exists but is not a directory, or if the directory creation fails.
   */
  public function create(): self {
    if (file_exists($this->path)) {
      if (is_dir($this->path)) {
        $this->logger?->info("Directory '{$this->path}' already exists");

        return $this;
      } else {
        throw new Exception("Path '{$this->path}' is not a directory", 400);
      }
    } else {
      if (!mkdir($this->path, 0777, true)) {
        throw new Exception("Failed to create directory '{$this->path}'", 500);
      } else {
        $this->logger?->info("Created directory '{$this->path}'");

        return $this;
      }
    }
  }

  /**
   * Scans the directory and returns an array of files and directories.
   *
   * @param int $sortingOrder The sorting order for the directory entries. Default is SCANDIR_SORT_ASCENDING.
   * @param resource|null $context The context resource to be used with the directory scan. Default is null.
   * @return array An array of files and directories in the directory, excluding '.' and '..'.
   */
  protected function scandir(int $sortingOrder = SCANDIR_SORT_ASCENDING, $context = null): array {
    return array_filter(
      scandir($this->path, $sortingOrder, $context),
      fn($fileOrDir) => !in_array($fileOrDir, ['.', '..'])
    );
  }

  /**
   * Lists all files and directories within the current directory.
   *
   * @param callable|null $filter A callback function used to filter the results. The function should accept a string parameter representing the relative path of each file or directory, and return a boolean indicating whether to include it in the result.
   * @param int $sortingOrder The sorting order of the files and directories. Use the `SCANDIR_SORT_ASCENDING` constant for ascending order, or `SCANDIR_SORT_DESCENDING` for descending order.
   * @param mixed $context Additional context information that can be passed to the `scandir` function.
   * @return array An array containing the relative paths of the files and directories that match the filter, if provided.
   */
  public function list(callable $filter = null, int $sortingOrder = SCANDIR_SORT_ASCENDING, $context = null): array {
    if ($this->exists()) {
      $filesAndDirectories = $this->scandir($sortingOrder, $context);

      return array_reduce($filesAndDirectories, function(array $files, string $fileOrDir) use ($filter) {
        $fileOrDirPath = $this->path.DIRECTORY_SEPARATOR.$fileOrDir;
        $this->logger?->info("Checking file '{$fileOrDirPath}'");

        if (is_dir($fileOrDirPath)) {
          $this->logger?->info("Directory '{$fileOrDirPath}' found");

          return [...$files, ...array_map(fn(string $value) => $fileOrDir.DIRECTORY_SEPARATOR.$value, (new Directory($fileOrDirPath))->list($filter))];
        } else {
          $this->logger?->info("File '{$fileOrDirPath}' found");
          $relativePath = substr($fileOrDirPath, strlen($this->path) + 1);

          if (!$filter || $filter && $filter($relativePath)) {
            return [...$files, $relativePath];
          } else {
            return $files;
          }
        }
      }, []);
    } else {
      return [];
    }
  }

  /**
   * Deletes the directory and its contents.
   *
   * If the directory exists, it will be recursively scanned to retrieve all files and subdirectories.
   * Each file will be deleted using the `unlink()` function, and each subdirectory will be deleted
   * recursively by calling the `delete()` method on a new `Directory` instance.
   * Finally, the directory itself will be deleted using the `rmdir()` function.
   *
   * @throws Exception If any file or directory deletion fails.
   */
  public function delete(): void {
    if ($this->exists()) {
      $filesAndDirectories = $this->scandir();
      $this->logger?->info("Purging directory '{$this->path}'", ['files' => $filesAndDirectories]);

      foreach ($filesAndDirectories as $file) {
        $filePath = $this->path.DIRECTORY_SEPARATOR.$file;

        if (is_dir($filePath)) {
          $subDirectory = new Directory($filePath);
          $subDirectory->delete();
        } else {
          if (!unlink($filePath)) {
            throw new Exception("Failed to delete file '{$filePath}'", 500); // @codeCoverageIgnore
          }
        }
      }

      if (!rmdir($this->path)) {
        throw new Exception("Failed to delete directory '{$this->path}'", 500); // @codeCoverageIgnore
      } else {
        $this->logger?->info("Deleted directory '{$this->path}'"); // @codeCoverageIgnore
      }
    } else {
      $this->logger?->info("Directory '{$this->path}' does not exist");
    }
  }
}

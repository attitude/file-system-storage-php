<?php declare(strict_types=1);

use Attitude\CLILogger\Logger;
use Attitude\FileSystemStorage\Blob\File;
use Attitude\FileSystemStorage\Blob\Directory;

$logger = new Logger();

it('creates a directory', function() use ($logger) {
  $path = STORAGE_DIR.'/path/to/directory';

  $directory = new Directory($path);
  // $directory->setLogger($logger);

  expect($directory)->toBeInstanceOf(Directory::class);
  expect($directory->path)->toBe($path);
  expect($directory->exists())->toBeFalse();

  $instance1 = $directory->create();
  expect($directory->exists())->toBeTrue();
  expect($directory)->toBe($instance1);

  $instance2 = $directory->create();
  expect($directory)->toBe($instance2);

  $directory->delete();
  expect($directory->exists())->toBeFalse();

  $file = (new File($path))->create();

  expect(fn() => $directory->exists())->toThrow('not a directory');
  expect(fn() => $directory->create())->toThrow('not a directory');

  $file->delete();
});

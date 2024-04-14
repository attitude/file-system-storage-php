<?php declare(strict_types=1);

use Attitude\CLILogger\Logger;
use Attitude\FileSystemStorage\Blob\File;
use Attitude\FileSystemStorage\Blob\Directory;

$logger = new Logger();


it('creates and deletes file', function() use ($logger) {
  $path = STORAGE_DIR.'/path/to/file.txt';

  $file = new File($path);
  // $file->setLogger($logger);

  expect($file)->toBeInstanceOf(File::class);
  expect($file->path)->toBe($path);
  expect($file->exists())->toBeFalse();

  $instance1 = $file->create();
  expect($file->exists())->toBeTrue();
  expect($file)->toBe($instance1);

  $instance2 = $file->create();
  expect($file)->toBe($instance2);

  $file->delete();
  expect($file->exists())->toBeFalse();

  $file->delete();
  expect($file->exists())->toBeFalse();

  $directory = (new Directory($path))->create();
  expect(fn() => $file->exists())->toThrow('not a file');
  expect(fn() => $file->create())->toThrow('not a file');

  $directory->delete();

  $file->touch();
  expect($file->exists())->toBeTrue();
  $file->delete();
  expect($file->exists())->toBeFalse();
});

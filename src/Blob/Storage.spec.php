<?php declare(strict_types=1);

use Attitude\FileSystemStorage\Blob\Storage;
use Attitude\FileSystemStorage\Blob\File;
use Attitude\FileSystemStorage\Serializer\JSONSerializer;

it('fails to create root Storage instance', function () {
    $path = '/devnull';
    expect(function () use ($path) {
        new Storage($path);
    })->toThrow("Failed to create directory '{$path}'");
});

it('creates a new Storage instance', function () {
    $path = 'path/to/storage';
    $storage = useBlobStorage($path);

    expect($storage->directory->path)->toBe(STORAGE_DIR . DIRECTORY_SEPARATOR . $path);
    expect($storage)->toBeInstanceOf(Storage::class);
    expect($storage->path)->toBe(STORAGE_DIR . DIRECTORY_SEPARATOR . $path);
});

it('returns a File instance', function () {
    $path = 'path/to/storage';
    $filename = 'test.txt';
    $serializer = new JSONSerializer();
    $storage = useBlobStorage($path);

    $file = $storage->file($filename, $serializer);

    expect($file)->toBeInstanceOf(File::class);
    expect($file->path)->toBe(STORAGE_DIR . DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $filename);
    expect($file->serializer)->toBe($serializer);
});

it('returns a list of files', function () {
    $path = 'path/to/storage';
    $storage = useBlobStorage($path);
    $serializer = new JSONSerializer();

    $storage->file('file1.txt', $serializer)->create();
    $storage->file('file2.txt', $serializer)->create();
    $storage->file('file3.txt', $serializer)->create();

    $files = $storage->list();

    expect($files)->toBeArray();
    expect($files)->toHaveCount(3);
    expect($files)->toContain('file1.txt');
    expect($files)->toContain('file2.txt');
    expect($files)->toContain('file3.txt');

    // Clean up
    $storage->file('file1.txt', $serializer)->delete();
    $storage->file('file2.txt', $serializer)->delete();
    $storage->file('file3.txt', $serializer)->delete();

    $files = $storage->list();

    expect($files)->toBeArray();
    expect($files)->toHaveCount(0);
});

it('removes an empty directory', function () {
    $path = 'path/to/storage';
    $file = 'some/path/to/the/file.txt';
    $storage = useBlobStorage($path);
    $file = $storage->file($file, new JSONSerializer())->create()->delete();

    $result = $storage->clearParents($file);

    expect($result)->toBeTrue();
});

it('returns false when removing a non-empty directory', function () {
    $path = 'path/to/storage';
    $storage = useBlobStorage($path);
    $file1 = $storage->file("foo/bar/baz/hello/world.txt", new JSONSerializer())->create();

    expect(fn() => $storage->clearParents($file1))->toThrow("still exists");
    expect(fn() => $storage->clearParents(new File('random.txt', new JSONSerializer())))->toThrow("is not in storage");

    $file1->delete();

    $storage->file("foo/hello/world.tsx", new JSONSerializer())->create();
    $storage->file("hello/world.tsx", new JSONSerializer())->create();

    expect($storage->clearParents($file1))->toBeFalse();
});

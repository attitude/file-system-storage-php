<?php declare(strict_types=1);

namespace Attitude\FileSystemStorage;

describe('KeyValueStorage', function() {
  it('sets item to storage', function() {
    $storage = useKeyValueStorage('kv');
    expect($storage->set('key', 'value'))->toBeNull();
  });

  it('checks item in storage', function() {
    $storage = useKeyValueStorage('kv');
    expect($storage->has('key'))->toBe(true);
  });

  it('gets item from storage', function() {
    $storage = useKeyValueStorage('kv');
    $storage->set('key', 'value');
    expect($storage->get('key'))->toBe('value');
  });

  it('deletes item in storage', function() {
    $storage = useKeyValueStorage('kv');
    $storage->set('key', 'value');

    touch(STORAGE_DIR.DIRECTORY_SEPARATOR.'kv'.DIRECTORY_SEPARATOR.'.DS_Store');
    $storage->delete('key');

    expect($storage->has('key'))->toBeFalse();
  });

  it('deletes sub-item in storage', function() {
    $storage = useKeyValueStorage('kv');
    $storage->set('sub/key1', 'value');
    $storage->set('sub/key2', 'value');

    touch(STORAGE_DIR.DIRECTORY_SEPARATOR.'kv'.DIRECTORY_SEPARATOR.'sub'.DIRECTORY_SEPARATOR.'.DS_Store');
    $storage->delete('sub/key1');
    expect($storage->has('sub/key1'))->toBeFalse();
    $storage->delete('sub/key2');
    expect($storage->has('sub/key2'))->toBeFalse();
  });

  it('throws not found exception', function() {
    $storage = useKeyValueStorage('kv');
    expect(fn() => $storage->get('invalid-key'))->toThrow("File '.storage/kv/invalid-key.json' does not exist");
  });

  it('returns all keys', function() {
    $storage = useKeyValueStorage('kv');
    $storage->set('key1', 'value1');
    $storage->set('key2', 'value2');
    expect($storage->keys())->toBe(['key1', 'key2']);
  });

  it('purges all items', function() {
    $storage = useKeyValueStorage('kv-purge');
    $storage->set('1/key1', 'value');
    $storage->set('1/key2', 'value');
    $storage->set('2/key1', 'value');
    $storage->set('2/key2', 'value');
    $storage->set('3/key1', 'value');
    $storage->set('3/key2', 'value');
    $storage->purge();

    expect($storage->keys())->toBe([]);
  });

  it('works in root directory', function() {
    $storage = useKeyValueStorage('.');
    $storage->set('root-key', 'value');
    expect($storage->get('root-key'))->toBe('value');
    expect($storage->keys())->toContain('root-key');

    $storage->delete('root-key');
    expect($storage->has('root-key'))->toBeFalse();
    expect($storage->keys())->not()->toContain('root-key');
    $storage->purge();
    $storage->purge();
  });
});

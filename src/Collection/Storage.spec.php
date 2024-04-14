<?php declare(strict_types=1);

namespace Attitude\FileSystemStorage;

function todayDateKey(): string{
  return (today())->format('Y/m/d');
}

it('constructs', function() {
  $storage = useCollectionsStorage('collection');

  expect($storage)->toBeInstanceOf(Collection\Storage::class);
  expect($storage->identifiers)->toBeInstanceOf(Collection\Identifier::class);
  expect($storage->storage)->toBeInstanceOf(KeyValue\Storage::class);
});

it('stores', function() {
  $collectionsStorage = useCollectionsStorage('collection');
  expect($collectionsStorage->store(['id' => '1', 'content' => 'value']))->toBeString();
});

it('has item to storage', function() {
  $collectionsStorage = useCollectionsStorage('collection');
  $key = $collectionsStorage->store(['id' => '1', 'content' => 'value']);

  expect($key)->toBeString();
  expect($collectionsStorage->has($key))->toBeTrue();
});

it('gets item from storage', function() {
  $collectionsStorage = useCollectionsStorage('collection');
  $key = $collectionsStorage->store(['id' => '1', 'content' => 'value']);

  expect($collectionsStorage->get($key))->toMatchArray([
    'content' => 'value',
    'year' => (today())->format('Y'),
    'month' => (today())->format('m'),
    'day' => (today())->format('d'),
  ]);
});

it('deletes item in storage', function() {
  $collectionsStorage = useCollectionsStorage('collection');
  $key = $collectionsStorage->store(['id' => '1', 'content' => 'value']);
  expect($collectionsStorage->delete($key))->toBeNull();
});

it('deletes same item in storage twice', function() {
  $collectionsStorage = useCollectionsStorage('collection');
  $key = $collectionsStorage->store(['id' => '1', 'content' => 'value']);
  expect($collectionsStorage->delete($key))->toBeNull();
  expect($collectionsStorage->delete($key))->toBeNull();
});

it('throws not found exception', function() {
  $collectionsStorage = useCollectionsStorage('collection');
  expect(fn() => $collectionsStorage->get(todayDateKey()))->toThrow("File '.storage/collection/".todayDateKey().".json' does not exist");
});

it('returns all keys', function() {
  $collectionsStorage = useCollectionsStorage('collection');
  $key1 = $collectionsStorage->store(['id' => '1', 'content' => 'value']);
  $key2 = $collectionsStorage->store(['id' => '2', 'content' => 'value']);
  expect($collectionsStorage->all())->toBe(array_unique([$key1, $key2]));
});

it('purges all items', function() {
  $storage = useCollectionsStorage('purge-collection');
  $key1 = $storage->store(['id' => 1, 'key1' => 'value 1']);
  $key2 = $storage->store(['id' => 2, 'key2' => 'value 2']);
  $key3 = $storage->store(['id' => 3, 'key1' => 'value 3']);
  $key4 = $storage->store(['id' => 4, 'key2' => 'value 4']);
  $key5 = $storage->store(['id' => 5, 'key1' => 'value 5']);
  $key6 = $storage->store(['id' => 6, 'key2' => 'value 6']);
  expect($storage->all())->toBe([$key1, $key2, $key3, $key4, $key5, $key6]);
  $storage->purge();

  expect($storage->all())->toBe([]);
});

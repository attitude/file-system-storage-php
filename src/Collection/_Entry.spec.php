<?php declare(strict_types=1);

namespace Attitude\FileSystemStorage\Collection;

describe('KeyValueTuple', function() {
  it('constructs', function() {
    $entry = new Entry('key', 'value');
    expect($entry instanceof Entry)->toBeTrue();
  });

  it('is immutable', function() {
    $entry = new Entry('key', 'value');
    expect(function() use (&$entry) {
      $entry->key = 'new value';
    })->toThrow('Entry tuple is immutable');
  });

  it('unpacks as tuple', function() {
    $entry = new Entry('key', 'value');
    expect([$entry[0], $entry[1]])->toBe(['key', 'value']);
    expect((array) $entry)->toBe(['key', 'value']);
  });
});

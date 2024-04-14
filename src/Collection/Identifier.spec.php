<?php declare(strict_types=1);

namespace Attitude\FileSystemStorage;

require_once '_Entry.php';

describe('Identifier', function() {
  it('constructs', function() {
    $identifiers = new Collection\Identifier('posts/{year}/{month}/{slug}-{id}', function (object $item) {
      $now = new \DateTimeImmutable();

      $item->year = $item->year ?? $now->format('Y');
      $item->month = $item->month ?? $now->format('m');
      $item->slug = $item->slug ?? null;
      $item->id = $item->id ?? 1;

      return $item;
    });
    expect($identifiers instanceof Collection\Identifier)->toBeTrue();
    expect($identifiers->pattern)->toBe('posts/{year}/{month}/{slug}-{id}');
    expect($identifiers->placeholders)->toBe(['{year}', '{month}', '{slug}', '{id}']);

    return $identifiers;
  });

  it('invokes parse', function(Collection\Identifier $identifiers) {
    $parsed = $identifiers->parse((object) [
      'year' => '2021',
      'month' => '01',
      'slug' => 'hello-world',
    ]);

    expect($parsed instanceof Collection\Entry)->toBeTrue();

    return $parsed;
  })->depends('it constructs');

  it('returns parsed data', function(Collection\Entry $parsed) {
    expect($parsed->key)->toBe('posts/2021/01/hello-world-1');
    expect($parsed->value)->toEqual((object) [
      'year' => '2021',
      'month' => '01',
      'slug' => 'hello-world',
      'id' => 1,
    ]);
  })->depends('it invokes parse');

  it('throws exception for invalid pattern', function() {
    $closure = function() {
      new Collection\Identifier('posts/', function (): object {
        return (object) [];
      });
    };

    expect($closure)->toThrow(new \InvalidArgumentException('Identifiers pattern expects at least one `{placeholder}`'));
  });
});

<?php declare(strict_types=1);

namespace Attitude\FileSystemStorage\Serializer;

describe('PHPSerializer', function() {
  it('serializes a value correctly', function () {
    $serializer = new PHPSerializer();
    $value = ['name' => 'John', 'age' => 30];

    expect($serializer->serialize($value))->toBe('a:2:{s:4:"name";s:4:"John";s:3:"age";i:30;}');
  });

  it('deserializes a value correctly', function () {
    $serializer = new PHPSerializer();
    $value = 'a:2:{s:4:"name";s:4:"John";s:3:"age";i:30;}';

    expect($serializer->deserialize($value))->toBe(['name' => 'John', 'age' => 30]);
  });

  it('returns the correct file extension', function () {
    $serializer = new PHPSerializer();
    $expected = 'php.txt';

    expect($serializer->getExtension())->toBe($expected);
  });
});

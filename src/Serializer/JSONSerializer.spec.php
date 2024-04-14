<?php declare(strict_types=1);

namespace Attitude\FileSystemStorage\Serializer;

describe('JSONSerializer', function() {
  it('serializes a value correctly', function () {
    $serializer = new JSONSerializer();
    $value = ['name' => 'John', 'age' => 30];

    expect($serializer->serialize($value))->toBe('{"name":"John","age":30}');
  });

  it('deserializes a value correctly', function () {
    $serializer = new JSONSerializer();
    $value = '{"name":"John","age":30}';

    expect($serializer->deserialize($value))->toBe(['name' => 'John', 'age' => 30]);
  });

  it('returns the correct file extension', function () {
    $serializer = new JSONSerializer();
    $expected = 'json';

    expect($serializer->getExtension())->toBe($expected);
  });
});

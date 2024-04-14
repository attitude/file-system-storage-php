<?php declare(strict_types=1);

namespace Attitude\FileSystemStorage\Serializer;

interface Serializer {
  public function serialize(mixed $value): string;
  public function deserialize(string $value): mixed;
  public function getExtension(): string;
}

<?php

namespace Statamic\Auth\WebAuthn;

use Symfony\Component\Serializer\SerializerInterface;

class Serializer
{
    public function __construct(private SerializerInterface $serializer)
    {
        //
    }

    public function serialize(mixed $data, string $format, array $context = []): string
    {
        return $this->serializer->serialize($data, $format, $context);
    }

    public function deserialize(mixed $data, string $type, string $format, array $context = []): mixed
    {
        return $this->serializer->deserialize($data, $type, $format, $context);
    }

    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        return $this->serializer->normalize($data, $format, $context);
    }
}

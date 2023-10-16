<?php

namespace App\Serializer;

use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class SerializerFactory
{
    public function create(): Serializer{
        $encoders = [new JsonEncoder()];
        $normalizer = new ObjectNormalizer(null, null, null, new ReflectionExtractor());
        return new Serializer([new DateTimeNormalizer(), $normalizer],$encoders);
    }
}
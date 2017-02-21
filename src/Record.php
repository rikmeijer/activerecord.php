<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 17-2-17
 * Time: 10:48
 */
namespace ActiveRecord;

interface Record
{
    public function references(string $referenceIdentifier, string $referencedEntityTypeIdentifier, array $conditions);

    public function contains(array $values);

    public function requires(array $attributeIdentifiers);

    public function missesRequiredValues() : bool;

    /**
     * @param string $property
     */
    public function __get($property);

    public function read(string $entityTypeIdentifier, array $conditions): array;

    public function readFirst(string $entityTypeIdentifier, array $conditions): \ActiveRecord\Record;

    /**
     * @param string $property
     * @param string $value
     */
    public function __set($property, $value);

    /**
     */
    public function delete() : int;

    public function create() : int;

    public function __call(string $method, array $arguments);
}
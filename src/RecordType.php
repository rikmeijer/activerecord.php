<?php

namespace pulledbits\ActiveRecord;

interface RecordType
{
    public function makeRecord(array $values);

    public function primaryKey(array $values);

    public function update(array $changes, array $values): int;

    public function delete(array $conditions): int;

    public function create(array $values): int;

    public function call(string $procedureIdentifier, array $arguments): void;

    public function fetchBy(string $referenceIdentifier, array $values, array $conditions): array;

    public function referenceBy(string $referenceIdentifier, array $values, array $conditions): \pulledbits\ActiveRecord\Record;
}
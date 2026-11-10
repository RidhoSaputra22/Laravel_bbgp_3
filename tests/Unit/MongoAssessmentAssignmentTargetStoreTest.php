<?php

namespace Tests\Unit;

use App\Services\Assessment\MongoAssessmentAssignmentTargetStore;
use ReflectionProperty;
use Tests\TestCase;

class MongoAssessmentAssignmentTargetStoreTest extends TestCase
{
    public function test_school_metadata_uses_a_non_destructive_set_update(): void
    {
        config()->set('assessment_mongodb.enabled', true);
        config()->set('assessment_mongodb.uri', 'mongodb://127.0.0.1:27017');

        $collection = new class
        {
            public array $operations = [];

            public function bulkWrite(array $operations, array $options): object
            {
                $this->operations = $operations;

                return new class
                {
                    public function getMatchedCount(): int
                    {
                        return 1;
                    }

                    public function getModifiedCount(): int
                    {
                        return 1;
                    }
                };
            }
        };

        $store = new MongoAssessmentAssignmentTargetStore;
        $property = new ReflectionProperty($store, 'collection');
        $property->setValue($store, $collection);

        $this->assertSame(
            ['matched' => 1, 'modified' => 1],
            $store->updateSchoolMetadata([
                ['target_id' => 115844, 'school_name' => 'SD Negeri 1 Bone'],
            ])
        );

        $operation = $collection->operations[0]['updateOne'];
        $this->assertSame(['$set' => ['user.nama_sekolah' => 'SD Negeri 1 Bone']], $operation[1][0]);
        $this->assertSame(['$unset' => 'user.metadata.nama_sekolah'], $operation[1][1]);
        $fallback = $operation[1][2]['$set']['user.metadata']['$cond'][0]['$eq'][0]['$size']['$objectToArray']['$ifNull'][1];
        $this->assertInstanceOf(\stdClass::class, $fallback);
        $this->assertFalse($operation[2]['upsert']);
        $this->assertArrayNotHasKey('replaceOne', $collection->operations[0]);
        $this->assertArrayNotHasKey('deleteMany', $collection->operations[0]);
    }

    public function test_empty_school_name_is_written_as_null(): void
    {
        config()->set('assessment_mongodb.enabled', true);

        $collection = new class
        {
            public array $operations = [];

            public function bulkWrite(array $operations, array $options): object
            {
                $this->operations = $operations;

                return new class
                {
                    public function getMatchedCount(): int
                    {
                        return 1;
                    }

                    public function getModifiedCount(): int
                    {
                        return 1;
                    }
                };
            }
        };

        $store = new MongoAssessmentAssignmentTargetStore;
        $property = new ReflectionProperty($store, 'collection');
        $property->setValue($store, $collection);

        $store->updateSchoolMetadata([
            ['target_id' => 115844, 'school_name' => null],
        ]);

        $this->assertSame(
            ['$set' => ['user.nama_sekolah' => null]],
            $collection->operations[0]['updateOne'][1][0]
        );
    }
}

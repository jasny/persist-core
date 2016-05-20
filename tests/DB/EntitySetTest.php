<?php

namespace Jasny\DB;

use Jasny\DB\BasicEntity;
use Jasny\DB\EntitySet;

/**
 * @covers Jasny\DB\EntitySet
 */
class EntitySetTest extends \PHPUnit_Framework_TestCase
{
    protected function createEntitySet($entities)
    {
        return EntitySet::forClass(BasicEntity::class, $entities, null, EntitySet::ALLOW_DUPLICATES);
    }
    
    public function testGet()
    {
        $entities = [];
        
        $entities[0] = $this->getMockForAbstractClass(BasicEntity::class);
        $entities[0]->foo = 10;

        $entities[1] = $this->getMockForAbstractClass(BasicEntity::class);
        $entities[1]->foo = 22;

        $entitySet = $this->createEntitySet($entities);
        
        $this->assertEquals([10, 22], $entitySet->foo);
    }
    
    public function testGetAndFlatten()
    {
        $entities = [];
        
        $entities[0] = $this->getMockForAbstractClass(BasicEntity::class);
        $entities[0]->foo = 10;

        $entities[1] = $this->getMockForAbstractClass(BasicEntity::class);
        $entities[1]->foo = 22;

        $entitySet = $this->createEntitySet($entities);
        
        $this->assertEquals([10, 22], $entitySet->foo);
    }

    public function testGetEntities()
    {
        $entities = [];
        
        $a = $this->getMockForAbstractClass(BasicEntity::class);
        $b = $this->getMockForAbstractClass(BasicEntity::class);
        
        $entities[0] = $this->getMockForAbstractClass(BasicEntity::class);
        $entities[0]->foo = $a;

        $entities[1] = $this->getMockForAbstractClass(BasicEntity::class);
        $entities[1]->foo = $b;

        $entitySet = EntitySet::forClass(BasicEntity::class, $entities, null, EntitySet::ALLOW_DUPLICATES);
        
        $fooSet = $entitySet->foo;
        $this->assertInstanceOf(EntitySet::class, $fooSet);
        $this->assertEquals(EntitySet::ALLOW_DUPLICATES, $fooSet->getFlags());
        $this->assertSame([$a, $b], $fooSet->getArrayCopy());
    }

    public function testGetAndFlattenEntitySets()
    {
        $entities = [];
        
        $a1 = $this->getMockForAbstractClass(BasicEntity::class);
        $a2 = $this->getMockForAbstractClass(BasicEntity::class);
        $b = $this->getMockForAbstractClass(BasicEntity::class);
        
        $entities[0] = $this->getMockForAbstractClass(BasicEntity::class);
        $entities[0]->foo = $this->createEntitySet([$a1, $a2]);

        $entities[1] = $this->getMockForAbstractClass(BasicEntity::class);
        $entities[1]->foo = $this->createEntitySet([$b]);

        $entitySet = EntitySet::forClass(BasicEntity::class, $entities, null, EntitySet::ALLOW_DUPLICATES);
        
        $fooSet = $entitySet->foo;
        $this->assertInstanceOf(EntitySet::class, $fooSet);
        $this->assertEquals(EntitySet::ALLOW_DUPLICATES, $fooSet->getFlags());
        $this->assertSame([$a1, $a2, $b], $fooSet->getArrayCopy());
    }
}

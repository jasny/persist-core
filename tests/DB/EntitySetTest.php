<?php

namespace Jasny\DB;

use Jasny\DB\Entity;
use Jasny\DB\EntitySet;

/**
 * @covers EntitySet
 */
class EntitySetTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $entities = [];
        
        $entities[0] = $this->getMockForAbstractClass(Entity::class);
        $entities[0]->foo = 10;

        $entities[1] = $this->getMockForAbstractClass(Entity::class);
        $entities[1]->foo = 22;

        $entitySet = EntitySet::forClass(Entity::class, $entities);
        
        $this->assertEquals([10, 22], $entitySet->foo);
    }
}

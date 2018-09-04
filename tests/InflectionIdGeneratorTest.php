<?php

namespace Jasny\DB\Tests;

use Doctrine\Inflector\Inflector;
use Jasny\DB\CRUD\Container\InflectionIdGenerator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class InflectionIdGeneratorTest extends TestCase
{
    /**
     * @var Inflector|MockObject
     */
    protected $inflector;

    public function setUp()
    {
        $this->inflector = $this->createMock(Inflector::class);
    }


    public function testGetNamespace()
    {
        $idGenerator = new InflectionIdGenerator('App\\Model', $this->inflector);
        $this->assertEquals('App\\Model', $idGenerator->getNamespace());
    }

    public function testGetInflector()
    {
        $idGenerator = new InflectionIdGenerator('App\\Model', $this->inflector);
        $this->assertEquals($this->inflector, $idGenerator->getInflector());
    }


    public function namespaceProvider()
    {
        return [
            ['', '%s'],
            ['App', 'App\\%s'],
            ['App\\Model', 'App\\Model\\%s']
        ];
    }

    /**
     * @dataProvider namespaceProvider
     */
    public function testFromIdToEntityClass($ns, $classFormat)
    {
        $idGenerator = new InflectionIdGenerator($ns, $this->inflector);

        $this->inflector->expects($this->once())->method('singularize')
            ->with('client_types')->willReturn('client_type');

        $this->inflector->expects($this->once())->method('classify')
            ->with('client_type')->willReturn('ClientType');

        $result = $idGenerator->fromIdToEntityClass('client_types');

        $this->assertEquals(sprintf($classFormat, 'ClientType'), $result);
    }

    /**
     * @dataProvider namespaceProvider
     */
    public function testFromIdToGatewayClass($ns, $classFormat)
    {
        $idGenerator = new InflectionIdGenerator($ns, $this->inflector);

        $this->inflector->expects($this->once())->method('singularize')
            ->with('client_types')->willReturn('client_type');

        $this->inflector->expects($this->once())->method('classify')
            ->with('client_type')->willReturn('ClientType');

        $result = $idGenerator->fromIdToGatewayClass('client_types');

        $this->assertEquals(sprintf($classFormat, 'ClientTypeGateway'), $result);
    }

    /**
     * @dataProvider namespaceProvider
     */
    public function testFromGatewayClassToId($ns, $classFormat)
    {
        $idGenerator = new InflectionIdGenerator($ns, $this->inflector);

        $this->inflector->expects($this->once())->method('tableize')
            ->with('ClientType')->willReturn('client_type');

        $this->inflector->expects($this->once())->method('pluralize')
            ->with('client_type')->willReturn('client_types');

        $result = $idGenerator->fromGatewayClassToId(sprintf($classFormat, 'ClientTypeGateway'));

        $this->assertEquals('client_types', $result);
    }

    /**
     * @dataProvider namespaceProvider
     */
    public function testFromGatewayClassToEntityClass($ns, $classFormat)
    {
        $idGenerator = new InflectionIdGenerator($ns, $this->inflector);
        $result = $idGenerator->fromGatewayClassToEntityClass(sprintf($classFormat, 'ClientTypeGateway'));

        $this->assertEquals(sprintf($classFormat, 'ClientType'), $result);
    }

    /**
     * @dataProvider namespaceProvider
     */
    public function testFromEntityClassToId($ns, $classFormat)
    {
        $idGenerator = new InflectionIdGenerator($ns, $this->inflector);

        $this->inflector->expects($this->once())->method('tableize')
            ->with('ClientType')->willReturn('client_type');

        $this->inflector->expects($this->once())->method('pluralize')
            ->with('client_type')->willReturn('client_types');

        $result = $idGenerator->fromEntityClassToId(sprintf($classFormat, 'ClientType'));

        $this->assertEquals('client_types', $result);
    }

    /**
     * @dataProvider namespaceProvider
     */
    public function testFromEntityClassToGatewayClass($ns, $classFormat)
    {
        $idGenerator = new InflectionIdGenerator($ns, $this->inflector);
        $result = $idGenerator->fromEntityClassToGatewayClass(sprintf($classFormat, 'ClientType'));

        $this->assertEquals(sprintf($classFormat, 'ClientTypeGateway'), $result);
    }


    /**
     * @dataProvider namespaceProvider
     * @expectedException UnexpectedValueException
     */
    public function testFromGatewayClassToIdInvalidNamespace($ns)
    {
        $this->expectExceptionMessage("Class 'Foo\\BarGateway' is not in "
            . ($ns === '' ? "root namespace" : "expected namespace '$ns'"));

        $idGenerator = new InflectionIdGenerator($ns, $this->inflector);

        $idGenerator->fromGatewayClassToId('Foo\\BarGateway');
    }

    /**
     * @dataProvider namespaceProvider
     * @expectedException UnexpectedValueException
     */
    public function testFromEntityClassToIdInvalidNamespace($ns)
    {
        $this->expectExceptionMessage("Class 'Foo\\Bar' is not in "
            . ($ns === '' ? "root namespace" : "expected namespace '$ns'"));

        $idGenerator = new InflectionIdGenerator($ns, $this->inflector);

        $idGenerator->fromEntityClassToId('Foo\\Bar');
    }

    /**
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage Invalid gateway class name 'Bar': should end in 'Gateway'
     */
    public function testFromGatewayClassToIdInvalidClassname()
    {
        $idGenerator = new InflectionIdGenerator('', $this->inflector);
        $idGenerator->fromGatewayClassToId('Bar');
    }

    /**
     * @expectedException UnexpectedValueException
     * @expectedExceptionMessage Invalid gateway class name 'Bar': should end in 'Gateway'
     */
    public function testFromGatewayClassToEntityClassInvalidClassname()
    {
        $idGenerator = new InflectionIdGenerator('', $this->inflector);
        $idGenerator->fromGatewayClassToEntityClass('Bar');
    }


    public function testCreateDefault()
    {
        $idGenerator = InflectionIdGenerator::createDefault('App');

        $this->assertEquals('App', $idGenerator->getNamespace());

        $inflector = $idGenerator->getInflector();
        $this->assertInstanceOf(Inflector::class, $inflector);

        $this->assertEquals('App\\ClientType', $idGenerator->fromIdToEntityClass('client_types'));
        $this->assertEquals('App\\ClientTypeGateway', $idGenerator->fromIdToGatewayClass('client_types'));
        $this->assertEquals('client_types', $idGenerator->fromEntityClassToId('App\\ClientType'));
        $this->assertEquals('client_types', $idGenerator->fromGatewayClassToId('App\ClientTypeGateway'));

        $this->assertEquals('aliases', $idGenerator->fromEntityClassToId('App\\Alias'));
        $this->assertEquals('cacti', $idGenerator->fromEntityClassToId('App\\Cactus'));
    }
}

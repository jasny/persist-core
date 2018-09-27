<?php

namespace Jasny\DB\Tests\Gateway;

use Jasny\DB\CRUD\CRUDInterface;
use Jasny\DB\Fetcher\FetcherInterface;
use Jasny\DB\Gateway\CompositeGateway;
use Jasny\DB\Search\SearchInterface;
use Jasny\Entity\EntityInterface;
use Jasny\Entity\Trigger\TriggerSet;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Jasny\DB\Gateway\CompositeGateway
 */
class CompositeGatewayTest extends TestCase
{
    /** @var CompositeGateway */
    protected $gateway;

    /** @var \stdClass */
    protected $source;

    /** @var TriggerSet|MockObject */
    protected $triggerSet;

    /** @var CRUDInterface|MockObject */
    protected $crud;

    /** @var FetcherInterface|MockObject */
    protected $fetch;

    /** @var SearchInterface|MockObject */
    protected $search;


    public function setUp()
    {
        $this->source = new \stdClass();
        $this->triggerSet = $this->createMock(TriggerSet::class);

        $this->crud = $this->createMock(CRUDInterface::class);
        $this->fetch = $this->createMock(FetcherInterface::class);
        $this->search = $this->createMock(SearchInterface::class);

        $this->gateway = new CompositeGateway(
            $this->source,
            $this->triggerSet,
            $this->crud,
            $this->fetch,
            $this->search
        );
    }


    public function testCreate()
    {
        $entity = $this->createMock(EntityInterface::class);
        $this->crud->expects($this->once())->method('create')->with('foo', 'bar')->willReturn($entity);
        $this->triggerSet->expects($this->once())->method('apply')->with($entity);

        $this->gateway->create('foo', 'bar');
    }

    public function testExists()
    {

    }

    public function testDelete()
    {

    }

    public function testFetchList()
    {

    }

    public function testCount()
    {

    }

    public function testFetch()
    {

    }

    public function testSave()
    {

    }

    public function testFetchAll()
    {

    }

    public function testSearch()
    {

    }

    public function testFetchPairs()
    {

    }
}

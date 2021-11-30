<?php

namespace Tests;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Laragear\Rut\Exceptions\EmptyRutException;
use Laragear\Rut\Facades\Generator;
use Laragear\Rut\Rut;
use Orchestra\Testbench\TestCase;

class RequestMacrosTest extends TestCase
{
    use RegistersPackage;

    public function test_request_retrieves_rut_from_input(): void
    {
        $request = Request::create('/path', 'POST');

        $request->request->set('rut', ($rut = Generator::makeOne())->format());

        static::assertInstanceOf(Rut::class, $request->rut());
        static::assertTrue($rut->isEqual($request->rut()));
    }


    public function test_request_retrieves_rut_from_query(): void
    {
        $request = Request::create('/path');

        $request->query->set('rut', ($rut = Generator::makeOne())->format());

        static::assertInstanceOf(Rut::class, $request->rut());
        static::assertTrue($rut->isEqual($request->rut()));
    }

    public function test_request_retrieves_rut_from_named_input(): void
    {
        $request = Request::create('/path', 'POST');

        $request->request->set('foo', ($rut = Generator::makeOne())->format());

        static::assertInstanceOf(Rut::class, $request->rut('foo'));
        static::assertTrue($rut->isEqual($request->rut('foo')));
    }

    public function test_request_retrieves_rut_from_named_query(): void
    {
        $request = Request::create('/path');

        $request->query->set('foo', ($rut = Generator::makeOne())->format());

        static::assertInstanceOf(Rut::class, $request->rut('foo'));
        static::assertTrue($rut->isEqual($request->rut('foo')));
    }

    public function test_throws_exception_if_input_or_query_null(): void
    {
        $this->expectException(EmptyRutException::class);
        $this->expectExceptionMessage('The RUT needs at least 7 valid characters, 0 given.');

        $request = Request::create('/path', 'POST');

        $request->rut();
    }

    public function test_retrieves_rut_collection_from_input(): void
    {
        $request = Request::create('/path', 'POST');

        $request->request->set('ruts', Generator::make(5)->map->toString()->toArray());

        static::assertInstanceOf(Collection::class, $request->ruts());
        static::assertCount(5, $request->ruts());
        static::assertInstanceOf(Rut::class, $request->ruts()->first());
    }

    public function test_retrieves_rut_collection_from_query(): void
    {
        $request = Request::create('/path');

        $request->query->set('ruts', Generator::make(5)->map->toString()->toArray());

        static::assertInstanceOf(Collection::class, $request->ruts());
        static::assertCount(5, $request->ruts());
        static::assertInstanceOf(Rut::class, $request->ruts()->first());
    }

    public function test_retrieves_rut_collection_from_named_input()
    {
        $request = Request::create('/path', 'POST');

        $request->request->set('foo', Generator::make(5)->map->toString()->toArray());

        static::assertInstanceOf(Collection::class, $request->ruts('foo'));
        static::assertCount(5, $request->ruts('foo'));
        static::assertInstanceOf(Rut::class, $request->ruts('foo')->first());
    }

    public function test_retrieves_rut_collection_from_named_query(): void
    {
        $request = Request::create('/path');

        $request->query->set('foo', Generator::make(5)->map->toString()->toArray());

        static::assertInstanceOf(Collection::class, $request->ruts('foo'));
        static::assertCount(5, $request->ruts('foo'));
        static::assertInstanceOf(Rut::class, $request->ruts('foo')->first());
    }

    public function test_empty_collection_if_input_or_query_null(): void
    {
        $request = Request::create('/path');

        static::assertInstanceOf(Collection::class, $request->ruts());
        static::assertEmpty($request->ruts());
    }

    public function test_throws_if_one_rut_is_invalid(): void
    {
        $this->expectException(EmptyRutException::class);
        $this->expectExceptionMessage('The RUT needs at least 7 valid characters, 1 given.');

        $request = Request::create('/path', 'POST');

        $request->request->set(
            'ruts',
            Generator::make(4)->push(new Rut(0, ''))->map->toString()->toArray()
        );

        $request->ruts();
    }
}
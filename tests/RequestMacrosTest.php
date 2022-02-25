<?php

namespace Tests;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Laragear\Rut\Exceptions\EmptyRutException;
use Laragear\Rut\Facades\Generator;
use Laragear\Rut\Rut;

class RequestMacrosTest extends TestCase
{
    public function test_request_retrieves_rut_from_input(): void
    {
        $request = Request::create('/path', 'POST');

        $request->request->set('rut', ($rut = Generator::makeOne())->format());

        static::assertInstanceOf(Rut::class, $request->rut());
        static::assertTrue($rut->isEqual($request->rut()));
    }

    public function test_request_retrieves_collection_from_multiple_inputs(): void
    {
        $request = Request::create('/path', 'POST');

        $request->request->set('foo', (Generator::makeOne())->format());
        $request->request->set('bar', (Generator::makeOne())->format());
        $request->request->set('baz', (Generator::makeOne())->format());

        $ruts = $request->rut('foo', 'bar', 'baz', 'quz');

        static::assertInstanceOf(Collection::class, $ruts);
        static::assertCount(3, $ruts);
    }

    public function test_throws_exception_when_one_of_multiple_inputs_is_invalid(): void
    {
        $this->expectException(EmptyRutException::class);
        $this->expectExceptionMessage('The RUT needs at least 7 valid characters, 0 given.');

        $request = Request::create('/path', 'POST');

        $request->request->set('foo', (Generator::makeOne())->format());
        $request->request->set('bar', (Generator::makeOne())->format());
        $request->request->set('baz', 'invalid');

        $request->rut('foo', 'bar', 'baz', 'quz');
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

        $request->request->set('rut', Generator::make(5)->map->toString()->toArray());

        static::assertInstanceOf(Collection::class, $request->rut());
        static::assertCount(5, $request->rut());
        static::assertInstanceOf(Rut::class, $request->rut()->first());
    }

    public function test_retrieves_rut_collection_from_query(): void
    {
        $request = Request::create('/path');

        $request->query->set('rut', Generator::make(5)->map->toString()->toArray());

        static::assertInstanceOf(Collection::class, $request->rut());
        static::assertCount(5, $request->rut());
        static::assertInstanceOf(Rut::class, $request->rut()->first());
    }

    public function test_retrieves_rut_collection_from_named_input()
    {
        $request = Request::create('/path', 'POST');

        $request->request->set('foo', Generator::make(5)->map->toString()->toArray());

        static::assertInstanceOf(Collection::class, $request->rut('foo'));
        static::assertCount(5, $request->rut('foo'));
        static::assertInstanceOf(Rut::class, $request->rut('foo')->first());
    }

    public function test_retrieves_rut_collection_from_named_query(): void
    {
        $request = Request::create('/path');

        $request->query->set('foo', Generator::make(5)->map->toString()->toArray());

        static::assertInstanceOf(Collection::class, $request->rut('foo'));
        static::assertCount(5, $request->rut('foo'));
        static::assertInstanceOf(Rut::class, $request->rut('foo')->first());
    }

    public function test_retrieves_rut_collection_from_many_inputs(): void
    {
        $request = Request::create('/path', 'POST');

        $request->request->set('foo', Generator::makeOne()->format());
        $request->query->set('bar', Generator::makeOne()->format());

        static::assertInstanceOf(Collection::class, $request->rut(['foo', 'bar']));
        static::assertCount(2, $request->rut(['foo', 'bar']));
    }

    public function test_retrieves_rut_collection_from_nested_wildcard_input(): void
    {
        $request = Request::create('/path', 'POST');

        $request->request->set('foo', ['bar' => Generator::make(2)->map->format()->toArray()]);

        static::assertInstanceOf(Collection::class, $request->rut('foo.bar.*'));
        static::assertCount(2, $request->rut('foo.bar.*'));
    }

    public function test_throws_if_input_or_query_null(): void
    {
        $this->expectException(EmptyRutException::class);
        $this->expectExceptionMessage('The RUT needs at least 7 valid characters, 0 given.');

        $request = Request::create('/path');

        static::assertInstanceOf(Collection::class, $request->rut());
        static::assertEmpty($request->rut());
    }

    public function test_throws_if_one_rut_is_invalid(): void
    {
        $this->expectException(EmptyRutException::class);
        $this->expectExceptionMessage('The RUT needs at least 7 valid characters, 1 given.');

        $request = Request::create('/path', 'POST');

        $request->request->set(
            'rut',
            Generator::make(4)->push(new Rut(0, ''))->map->toString()->toArray()
        );

        $request->rut();
    }
}

<?php

declare(strict_types=1);

namespace tests\oihana\routes\helpers;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use ReflectionException;
use ReflectionFunction;
use function oihana\routes\helpers\responsePassthrough;

final class ResponsePassthroughTest extends TestCase
{
    /**
     * Test that responsePassthrough returns a callable.
     */
    public function testReturnsCallable(): void
    {
        $handler = responsePassthrough();

        $this->assertIsCallable( $handler );
    }

    /**
     * Test that the handler returns the response unchanged.
     */
    public function testReturnsResponseUnchanged(): void
    {
        $request = $this->createStub( ServerRequestInterface::class );
        $response = $this->createStub( ResponseInterface::class );

        $handler = responsePassthrough();
        $result = $handler( $request , $response );

        $this->assertSame( $response , $result );
    }

    /**
     * Test that the handler ignores the request parameter.
     */
    public function testIgnoresRequest(): void
    {
        $request = $this->createStub( ServerRequestInterface::class );
        $response = $this->createStub( ResponseInterface::class );

        $request->method( $this->anything() );

        $handler = responsePassthrough();
        $result = $handler( $request , $response );

        $this->assertSame( $response , $result );
    }

    /**
     * Test that multiple calls return the same response object.
     */
    public function testMultipleCallsReturnSameResponse(): void
    {
        $request = $this->createStub( ServerRequestInterface::class );
        $response = $this->createStub( ResponseInterface::class );

        $handler = responsePassthrough();

        $result1 = $handler( $request , $response );
        $result2 = $handler( $request , $response );

        $this->assertSame( $response , $result1 );
        $this->assertSame( $response , $result2 );
        $this->assertSame( $result1 , $result2 );
    }

    /**
     * Test that the handler can be used with different request/response pairs.
     */
    public function testWorksWithDifferentRequestResponsePairs(): void
    {
        $handler = responsePassthrough();

        $request1 = $this->createStub( ServerRequestInterface::class );
        $response1 = $this->createStub( ResponseInterface::class );

        $request2 = $this->createStub( ServerRequestInterface::class );
        $response2 = $this->createStub( ResponseInterface::class );

        $result1 = $handler( $request1 , $response1 );
        $result2 = $handler( $request2 , $response2 );

        $this->assertSame( $response1 , $result1 );
        $this->assertSame( $response2 , $result2 );
        $this->assertNotSame( $result1 , $result2 );
    }

    /**
     * Test that the returned callable has the correct type signature.
     * @throws ReflectionException
     */
    public function testCallableTypeSignature(): void
    {
        $handler = responsePassthrough();

        $reflection = new ReflectionFunction( $handler(...) );
        $parameters = $reflection->getParameters();
        $returnType = $reflection->getReturnType();

        // Check number of parameters
        $this->assertCount( 2 , $parameters );

        // Check first parameter (Request)
        $this->assertSame( 'request' , $parameters[0]->getName() );
        $this->assertSame( ServerRequestInterface::class , $parameters[0]->getType()->getName() );

        // Check second parameter (Response)
        $this->assertSame( 'response' , $parameters[1]->getName() );
        $this->assertSame( ResponseInterface::class , $parameters[1]->getType()->getName() );

        // Check return type
        $this->assertInstanceOf( \ReflectionNamedType::class , $returnType );
        $this->assertSame( ResponseInterface::class , $returnType->getName() );
    }
}
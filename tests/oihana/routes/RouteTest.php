<?php

namespace tests\oihana\routes;

use PHPUnit\Framework\TestCase;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Slim\App;

use Psr\Log\LoggerInterface;

use oihana\routes\Route;

class RouteTest extends TestCase
{
    protected Container       $container ;
    protected App             $app ;
    protected LoggerInterface $logger ;

    protected function setUp(): void
    {
        $this->app     = $this->createStub(App::class);
        $this->logger  = $this->createStub(LoggerInterface::class);
        $this->container = new Container() ;

        $this->container->set( App::class            , $this->app    ) ;
        $this->container->set( LoggerInterface::class, $this->logger ) ;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function testConstructorSetsDefaults(): void
    {
        $route = new Route($this->container);

        $this->assertSame(Route::DEFAULT_PREFIX, $route->prefix);
        $this->assertSame(Route::DEFAULT_OWNER_PLACEHOLDER, $route->ownerPlaceholder);
        $this->assertSame(Route::DEFAULT_ROUTE_PLACEHOLDER, $route->routePlaceholder);
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testConstructorAcceptsInitValues(): void
    {
        $route = new Route($this->container, [
            'name' => 'foo',
            'prefix' => 'bar',
            'suffix' => 'baz',
            'route' => '/api/test'
        ]);

        $this->assertSame('foo', $route->name);
        $this->assertSame('bar', $route->prefix);
        $this->assertSame('baz', $route->suffix);
        $this->assertSame('/api/test', $route->route);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function testDotifyReplacesSlashes(): void
    {
        $route = new Route($this->container);
        $this->assertSame('foo.bar', $route->dotify('foo/bar'));
        $this->assertSame('foobar', $route->dotify('foobar'));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function testGetRouteAlwaysStartsWithSlash(): void
    {
        $route = new Route($this->container, ['route' => 'foo/bar']);
        $this->assertSame('/foo/bar', $route->getRoute());

        $route = new Route($this->container, ['route' => '/foo/bar']);
        $this->assertSame('/foo/bar', $route->getRoute());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function testGetNameCombinesPrefixAndSuffix(): void
    {
        $route = new Route( $this->container,
        [
            'prefix' => 'api',
            'name'   => 'user.get',
            'suffix' => 'json',
        ]);

        $this->assertSame('api.user.get.json', $route->getName());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function testGetNameDotifiesRouteWhenNoName(): void
    {
        $route = new Route($this->container, [
            'route' => 'foo/bar',
        ]);

        $this->assertSame('api.foo.bar', $route->getName());
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function testCreateReturnsNullForInvalidInput(): void
    {
        $route = new Route($this->container);

        $this->assertNull($route->create(null));
        $this->assertNull($route->create(['not_associative']));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function testCreateReturnsSameInstanceForRouteObject(): void
    {
        $route = new Route($this->container);
        $new = $route->create($route);

        $this->assertSame($route, $new);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function testCreateReturnsNewRouteForDefinitionArray(): void
    {
        $route = new Route($this->container);
        $definition = [
            Route::CLAZZ => Route::class,
            Route::NAME => 'foo',
            Route::ROUTE => '/test'
        ];

        $newRoute = $route->create($definition);
        $this->assertInstanceOf(Route::class, $newRoute);
        $this->assertSame('foo', $newRoute->name);
        $this->assertSame('/test', $newRoute->route);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function testExecuteCallsCallable(): void
    {
        $route = new Route($this->container);
        $called = false;
        $route->execute(function () use (&$called) { $called = true; });
        $this->assertTrue($called);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function testExecuteCallsAllCallableInArray(): void
    {
        $route = new Route($this->container);
        $called = 0;
        $route->execute([
            function () use (&$called) { $called++; },
            function () use (&$called) { $called++; },
        ]);
        $this->assertSame(2, $called);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function testInvokeDoesNothingIfNoRoutes(): void
    {
        $route = new Route($this->container, ['routes' => []]);
        $this->expectNotToPerformAssertions();
        $route();
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     */
    public function testInvokeRunsNestedRoutes(): void
    {
        $child = new class( $this->container ) extends Route
        {
            public bool $invoked = false ;
            public function __invoke(): void { $this->invoked = true ; }
        };

        $parent = new Route( $this->container , [ 'routes' => [ $child ] ] ) ;
        $parent() ;

        $this->assertTrue( $child->invoked ) ;
    }
}
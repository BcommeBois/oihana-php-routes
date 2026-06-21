<?php

namespace tests\oihana\routes\http;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use oihana\routes\http\OptionsRoute;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Routing\Route;

final class OptionsRouteTest extends TestCase
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws DependencyException
     */
    public function testInvokeRegistersOptionsRoute()
    {
        $routePath = '/api/test';

        $container = new Container();
        AppFactory::setContainer($container);
        $app = AppFactory::create();

        $container->set(App::class, $app);

        $optionsRoute = new OptionsRoute($container,
        [
            'route' => ltrim($routePath, '/')
        ]);

        $optionsRoute();

        $routeCollector = $app->getRouteCollector();
        $routes = $routeCollector->getRoutes();

        $this->assertCount(1, $routes, 'A route must be registered');

        $registeredRoute = array_shift($routes); // first element in the associative array

        $this->assertInstanceOf(Route::class, $registeredRoute);

        $this->assertEquals( $routePath  ,  $registeredRoute->getPattern());
        $this->assertEquals( ['OPTIONS'] , $registeredRoute->getMethods() ) ;

        $registeredCallable = $registeredRoute->getCallable();
        $this->assertIsCallable($registeredCallable);

        $requestStub  = $this->createStub(Request::class);
        $responseStub = $this->createStub(Response::class);

        $actualHandler = $registeredCallable($requestStub, $responseStub);
        $this->assertIsCallable($actualHandler, 'The register callable must return a real handler (arrow function)');

        $result = $actualHandler($requestStub, $responseStub);
        $this->assertSame($responseStub, $result);
    }
}
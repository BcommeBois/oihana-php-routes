<?php

namespace oihana\routes\http;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use oihana\enums\http\HttpMethod;
use oihana\routes\Route;

/**
 * Abstract base class for routes mapped to a controller method.
 *
 * Handles the logic for resolving the controller and method.
 *
 * Child classes (GetRoute, DeleteRoute, etc.) implement `registerRoute` to register
 * themselves with Slim using the correct HTTP verb.
 *
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 * @package oihana\routes\http
 */
abstract class HttpMethodRoute extends Route
{
    /**
     * Creates a new MethodRoute instance.
     * @param Container $container The DI Container reference.
     * @param array $init The optional settings object.
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct( Container $container , array $init = [] )
    {
        parent::__construct( $container , $init );
        $this->initializeMethod( $init ) ;
    }

    /**
     * The name of the method to call in the controller to invoke with this route.
     * @var string|mixed
     */
    public string $method ;

    /**
     * The *default* controller method name to invoke.
     * Child classes MUST redefine this constant to
     * follow their convention (e.g., 'delete' for DeleteRoute).
     */
    public const string INTERNAL_METHOD = HttpMethod::get ;

    /**
     * Initialize the internal method property.
     * @param array $init
     * @return $this
     */
    public function initializeMethod( array $init = [] ):static
    {
        $this->method = $init[ static::METHOD ] ?? static::INTERNAL_METHOD ;
        return $this ;
    }

    /**
     * Main entry point.
     *
     * Validates the controller/method and calls the `registerRoute` template method.
     *
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function __invoke(): void
    {
        if( !$this->container->has( $this->controllerID ) )
        {
            $this->warning( $this . ' invoke failed, the controller "' . $this->controllerID . '" is not registered in the DI container.' );
            return;
        }

        $controller = $this->container->get( $this->controllerID ) ;

        if( !isset( $controller ) || !method_exists( $controller , $this->method ) )
        {
            $this->warning( $this . ' invoke failed, the method "' . $this->method . '" is not defined in the controller "' . $this->controllerID . '".' );
            return;
        }

        $this->registerRoute( [ $controller , $this->method ] ) ;
    }

    /**
     * Template method (abstract) that children must implement.
     * This is where the actual route registration (get, post, delete...) occurs.
     *
     * @param callable $handler The resolved callable (e.g., [$controller, 'methodName'])
     */
    protected abstract function registerRoute( callable $handler ):void ;
}
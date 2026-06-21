<?php

namespace oihana\routes\traits;

use oihana\routes\enums\RouteFlag;
use oihana\routes\Route;
use function oihana\core\bits\hasFlag;

/**
 * Adds route-flag management to a route definition.
 *
 * This trait stores the {@see RouteFlag} bitmask describing which HTTP routes
 * are enabled and exposes a convenient API to query it (`hasGet()`, `hasPost()`,
 * ...), describe it in a human-readable form and mutate it (`enableFlags()`,
 * `disableFlags()`). The bitmask can be initialized from a raw integer or from
 * a legacy boolean-array configuration through {@see HasRouteTrait::initializeFlags()}.
 *
 * @package oihana\routes\traits
 * @author  Marc Alcaraz (ekameleon)
 * @since   1.0.0
 */
trait HasRouteTrait
{
    /**
     * Bitmask representing enabled routes
     * @var int
     */
    public int $flags = RouteFlag::DEFAULT ;

    /**
     * Initialize the internal flags.
     *
     * @param array|int $init A raw bitmask, an init array carrying a `flags`
     *                        integer, or a legacy boolean-array configuration.
     * @return static Returns the current instance for method chaining.
     */
    public function initializeFlags( array|int $init = [] ) :static
    {
        if ( is_int( $init ) )
        {
            $this->flags = $init ;
            return $this ;
        }

        if (isset( $init[ Route::FLAGS ] ) && is_int( $init[ Route::FLAGS ] ) )
        {
            $this->flags = $init[ self::FLAGS ] ;
            return $this ;
        }

        $this->flags = RouteFlag::convertLegacyArray( $init ) ;

        return $this ;
    }

    /**
     * Check if COUNT route is enabled
     *
     * @return bool `true` if the COUNT route is enabled, `false` otherwise.
     */
    public function hasCount(): bool
    {
        return hasFlag( $this->flags , RouteFlag::COUNT ) ;
    }

    /**
     * Check if DELETE route is enabled
     *
     * @return bool `true` if the DELETE route is enabled, `false` otherwise.
     */
    public function hasDelete(): bool
    {
        return hasFlag( $this->flags , RouteFlag::DELETE ) ;
    }

    /**
     * Check if DELETE_MULTIPLE is enabled
     *
     * @return bool `true` if the DELETE_MULTIPLE route is enabled, `false` otherwise.
     */
    public function hasDeleteMultiple(): bool
    {
        return hasFlag( $this->flags , RouteFlag::DELETE_MULTIPLE ) ;
    }

    /**
     * Check if GET route is enabled
     *
     * @return bool `true` if the GET route is enabled, `false` otherwise.
     */
    public function hasGet(): bool
    {
        return hasFlag( $this->flags , RouteFlag::GET ) ;
    }

    /**
     * Check if LIST route is enabled
     *
     * @return bool `true` if the LIST route is enabled, `false` otherwise.
     */
    public function hasList(): bool
    {
        return hasFlag( $this->flags , RouteFlag::LIST ) ;
    }

    /**
     * Check if PATCH route is enabled
     *
     * @return bool `true` if the PATCH route is enabled, `false` otherwise.
     */
    public function hasPatch(): bool
    {
        return hasFlag( $this->flags , RouteFlag::PATCH ) ;
    }

    /**
     * Check if POST route is enabled
     *
     * @return bool `true` if the POST route is enabled, `false` otherwise.
     */
    public function hasPost(): bool
    {
        return hasFlag( $this->flags , RouteFlag::POST ) ;
    }

    /**
     * Check if PUT route is enabled
     *
     * @return bool `true` if the PUT route is enabled, `false` otherwise.
     */
    public function hasPut(): bool
    {
        return hasFlag( $this->flags , RouteFlag::PUT ) ;
    }

    /**
     * Get a human-readable description of enabled routes
     *
     * @return string Description of enabled routes
     */
    public function describeFlags(): string
    {
        return RouteFlag::describe( $this->flags ) ;
    }

    /**
     * Enable specific route flags
     *
     * @param int $flags Flags to enable
     * @return static Returns the current instance for method chaining.
     */
    public function enableFlags( int $flags ) :static
    {
        $this->flags |= $flags ;
        return $this ;
    }

    /**
     * Disable specific route flags
     *
     * @param int $flags Flags to disable
     * @return static Returns the current instance for method chaining.
     */
    public function disableFlags( int $flags ) :static
    {
        $this->flags &= ~$flags ;
        return $this ;
    }
}
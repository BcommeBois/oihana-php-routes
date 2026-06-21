<?php

namespace oihana\routes\traits;

use oihana\routes\enums\RouteFlag;
use oihana\routes\Route;
use function oihana\core\bits\hasFlag;

trait HasRouteTrait
{
    /**
     * Bitmask representing enabled routes
     * @var int
     */
    public int $flags = RouteFlag::DEFAULT ;

    /**
     * Initialize the internal flags.
     * @param array|int $init
     * @return static
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
     */
    public function hasCount(): bool
    {
        return hasFlag( $this->flags , RouteFlag::COUNT ) ;
    }

    /**
     * Check if DELETE route is enabled
     */
    public function hasDelete(): bool
    {
        return hasFlag( $this->flags , RouteFlag::DELETE ) ;
    }

    /**
     * Check if DELETE_MULTIPLE is enabled
     */
    public function hasDeleteMultiple(): bool
    {
        return hasFlag( $this->flags , RouteFlag::DELETE_MULTIPLE ) ;
    }

    /**
     * Check if GET route is enabled
     */
    public function hasGet(): bool
    {
        return hasFlag( $this->flags , RouteFlag::GET ) ;
    }

    /**
     * Check if LIST route is enabled
     */
    public function hasList(): bool
    {
        return hasFlag( $this->flags , RouteFlag::LIST ) ;
    }

    /**
     * Check if PATCH route is enabled
     */
    public function hasPatch(): bool
    {
        return hasFlag( $this->flags , RouteFlag::PATCH ) ;
    }

    /**
     * Check if POST route is enabled
     */
    public function hasPost(): bool
    {
        return hasFlag( $this->flags , RouteFlag::POST ) ;
    }

    /**
     * Check if PUT route is enabled
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
     * @return static
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
     * @return static
     */
    public function disableFlags( int $flags ) :static
    {
        $this->flags &= ~$flags ;
        return $this ;
    }
}
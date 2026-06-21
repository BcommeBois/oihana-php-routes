<?php

namespace oihana\routes\enums;

use oihana\reflect\traits\ConstantsTrait;
use function oihana\core\bits\hasFlag;

class RouteFlag
{
    use ConstantsTrait ;

    const string DEFAULT_FLAG        = 'defaultFlag'       ;
    const string HAS_COUNT           = 'hasCount'          ;
    const string HAS_DELETE          = 'hasDelete'         ;
    const string HAS_DELETE_MULTIPLE = 'hasDeleteMultiple' ;
    const string HAS_GET             = 'hasGet'            ;
    const string HAS_LIST            = 'hasList'           ;
    const string HAS_PATCH           = 'hasPatch'          ;
    const string HAS_POST            = 'hasPost'           ;
    const string HAS_PUT             = 'hasPut'            ;

    /**
     * No routes enabled
     * @var int
     */
    public const int NONE = 0 ;

    /**
     * Enable COUNT route
     * @var int
     */
    public const int COUNT = 1 << 0 ;

    /**
     * Enable DELETE route
     * @var int
     */
    public const int DELETE = 1 << 1 ;

    /**
     * Enable DELETE with multiple IDs support
     * @var int
     */
    public const int DELETE_MULTIPLE = 1 << 2 ;

    /**
     * Enable GET route
     * @var int
     */
    public const int GET = 1 << 3 ;

    /**
     * Enable LIST route
     * @var int
     */
    public const int LIST = 1 << 4 ;

    /**
     * Enable PATCH route
     * @var int
     */
    public const int PATCH = 1 << 5 ;

    /**
     * Enable POST route
     * @var int
     */
    public const int POST = 1 << 6 ;

    /**
     * Enable PUT route
     * @var int
     */
    public const int PUT = 1 << 7 ;

    /**
     * All valid flags combined (used for validation)
     */
    public const int ALL = self::COUNT
                         | self::DELETE
                         | self::DELETE_MULTIPLE
                         | self::GET
                         | self::LIST
                         | self::PATCH
                         | self::POST
                         | self::PUT
                         ;

    /**
     * Default routes: all CRUD operations enabled
     */
    public const int DEFAULT = self::COUNT
                             | self::DELETE
                             | self::DELETE_MULTIPLE
                             | self::GET
                             | self::LIST
                             | self::PATCH
                             | self::POST
                             | self::PUT
                             ;

    /**
     * Read-only routes (GET, LIST, COUNT)
     */
    public const int READ_ONLY = self::GET
                               | self::LIST
                               | self::COUNT
                               ;

    /**
     * Write routes (POST, PUT, PATCH, DELETE)
     */
    public const int WRITE = self::POST
                           | self::PUT
                           | self::PATCH
                           | self::DELETE
                           | self::DELETE_MULTIPLE
                           ;

    /**
     * Basic CRUD without count
     */
    public const int CRUD = self::GET
                          | self::LIST
                          | self::POST
                          | self::PUT
                          | self::DELETE
                          ;

    /**
     * The default list of flags.
     */
    public const array FLAGS =
    [
        self::COUNT,
        self::DELETE,
        self::DELETE_MULTIPLE,
        self::GET,
        self::LIST,
        self::PATCH,
        self::POST,
        self::PUT,
    ];

    /**
     * The list of flag's name.
     */
    public const array FLAGS_NAME =
    [
        self::COUNT           => 'COUNT' ,
        self::DELETE          => 'DELETE' ,
        self::DELETE_MULTIPLE => 'DELETE_MULTIPLE' ,
        self::GET             => 'GET' ,
        self::LIST            => 'LIST' ,
        self::PATCH           => 'PATCH' ,
        self::POST            => 'POST' ,
        self::PUT             => 'PUT' ,
    ];

    /**
     * Converts legacy boolean array format to bitmask.
     *
     * @param array $init Legacy initialization array
     *
     * @return int The resulting bitmask
     */
    public static function convertLegacyArray( array $init = [] ): int
    {
        $mask = 0;
        $defaultFlag = ( $init[ self::DEFAULT_FLAG ] ?? true ) === true ;

        $mapping =
        [
            self::HAS_COUNT           => self::COUNT ,
            self::HAS_DELETE          => self::DELETE ,
            self::HAS_DELETE_MULTIPLE => self::DELETE_MULTIPLE ,
            self::HAS_GET             => self::GET ,
            self::HAS_LIST            => self::LIST ,
            self::HAS_PATCH           => self::PATCH ,
            self::HAS_POST            => self::POST ,
            self::HAS_PUT             => self::PUT ,
        ];

        foreach ($mapping as $key => $flag)
        {
            if ( ( $init[$key] ?? $defaultFlag ) === true )
            {
                $mask |= $flag;
            }
        }

        return $mask ;
    }

    /**
     * Gets a human-readable description of the flags in a bitmask.
     *
     * @param int $mask The bitmask value to describe.
     * @param string $separator The separator between the flag descriptions.
     *
     * @return string A comma-separated (by default) string of flag names.
     *
     * @example
     * ```php
     * use oihana\routes\enums\RouteFlag;
     *
     * $mask = RouteFlag::GET | RouteFlag::POST;
     * echo RouteFlag::describe($mask); // Outputs: "GET, POST"
     * ```
     */
    public static function describe(int $mask, string $separator = ', '): string
    {
        if ( $mask === self::NONE )
        {
            return 'NONE' ;
        }

        $descriptions = [];

        foreach ( self::FLAGS_NAME as $flag => $name )
        {
            if ( hasFlag( $mask , $flag ) )
            {
                $descriptions[] = $name ;
            }
        }

        return implode( $separator , $descriptions ) ;
    }

    /**
     * Gets a list of all individual flags present in a bitmask.
     *
     * @param int $mask The bitmask value to decompose.
     *
     * @return array<int> An array of individual flag values present in the mask.
     *
     * @example
     * ```php
     * use oihana\routes\enums\RouteFlag;
     *
     * $mask = RouteFlag::GET | RouteFlag::POST | RouteFlag::PUT;
     * $flags = RouteFlag::getFlags($mask);
     * // Returns [8, 64, 128] (the individual flag values)
     * ```
     */
    public static function getFlags( int $mask ): array
    {
        $flags = [];

        foreach ( self::FLAGS as $flag )
        {
            if ( hasFlag( $mask , $flag ) )
            {
                $flags[] = $flag ;
            }
        }

        return $flags;
    }

    /**
     * Checks whether a specific flag is set in a bitmask.
     *
     * @param int $mask The bitmask value, potentially containing multiple flags combined with `|`.
     * @param int $flag The specific flag to check for in the mask.
     *
     * @return bool Returns `true` if the given flag is present in the mask, `false` otherwise.
     *
     * @example
     * ```php
     * use oihana\routes\enums\RouteFlag;
     *
     * $mask = RouteFlag::GET | RouteFlag::POST;
     *
     * RouteFlag::has($mask, RouteFlag::GET);   // Returns true
     * RouteFlag::has($mask, RouteFlag::PATCH); // Returns false
     * ```
     */
    public static function has( int $mask, int $flag ): bool
    {
        return ( $mask & $flag ) !== 0;
    }

    /**
     * Validates that a bitmask contains only valid RouteFlag values.
     *
     * @param int $mask The bitmask value to validate.
     *
     * @return bool Returns `true` if the mask contains only valid flags, `false` otherwise.
     *
     * @example
     * ```php
     * use oihana\routes\enums\RouteFlag;
     *
     * RouteFlag::isValid(RouteFlag::GET | RouteFlag::POST); // Returns true
     * RouteFlag::isValid(RouteFlag::DEFAULT);               // Returns true
     * RouteFlag::isValid(1024);                             // Returns false (invalid flag)
     * ```
     */
    public static function isValid( int $mask ): bool
    {
        return ( $mask & ~self::ALL ) === 0 ;
    }
}
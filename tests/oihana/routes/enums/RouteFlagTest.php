<?php

namespace tests\oihana\routes\enums;

use oihana\routes\enums\RouteFlag;

use PHPUnit\Framework\TestCase;

final class RouteFlagTest extends TestCase
{
    public function testGetFlagsDecomposesAMask(): void
    {
        $mask  = RouteFlag::GET | RouteFlag::POST | RouteFlag::PUT ;
        $flags = RouteFlag::getFlags( $mask ) ;

        $this->assertSame( [ RouteFlag::GET , RouteFlag::POST , RouteFlag::PUT ] , $flags ) ;
    }

    public function testGetFlagsReturnsEmptyArrayForNone(): void
    {
        $this->assertSame( [] , RouteFlag::getFlags( RouteFlag::NONE ) ) ;
    }

    public function testHasDetectsAFlag(): void
    {
        $mask = RouteFlag::GET | RouteFlag::POST ;

        $this->assertTrue ( RouteFlag::has( $mask , RouteFlag::GET ) ) ;
        $this->assertFalse( RouteFlag::has( $mask , RouteFlag::PATCH ) ) ;
    }

    public function testIsValidAcceptsKnownFlagsAndRejectsUnknown(): void
    {
        $this->assertTrue ( RouteFlag::isValid( RouteFlag::DEFAULT ) ) ;
        $this->assertTrue ( RouteFlag::isValid( RouteFlag::GET | RouteFlag::POST ) ) ;
        $this->assertFalse( RouteFlag::isValid( 1 << 20 ) ) ;
    }
}

<?php

declare(strict_types=1);

namespace tests\oihana\routes\helpers;

use PHPUnit\Framework\TestCase;
use function oihana\routes\helpers\withPlaceholder;

final class WithPlaceholderTest extends TestCase
{
    public function testRequiredPlaceholder(): void
    {
        $route = '/users';
        $result = withPlaceholder($route, 'id');
        $this->assertSame('/users/{id}', $result);

        $result = withPlaceholder($route, 'id:[0-9]+');
        $this->assertSame('/users/{id:[0-9]+}', $result);
    }

    public function testOptionalPlaceholder(): void
    {
        $route = '/users';
        $result = withPlaceholder($route, 'id', true);
        $this->assertSame('/users[/{id}]', $result);

        $result = withPlaceholder($route, 'id:[0-9]+', true);
        $this->assertSame('/users[/{id:[0-9]+}]', $result);
    }

    public function testMultiSegmentPlaceholder(): void
    {
        $route = '/news';
        $result = withPlaceholder($route, 'params:.*');
        $this->assertSame('/news/{params:.*}', $result);

        $result = withPlaceholder($route, 'params:.*', true);
        $this->assertSame('/news[/{params:.*}]', $result);
    }

    public function testLeadingSlash(): void
    {
        $route = '/users';
        $result = withPlaceholder($route, 'id', false, false);
        $this->assertSame('/users{id}', $result); // Pas de slash ajouté devant
    }

    public function testAlreadyWrappedPlaceholder(): void
    {
        $route = '/users';
        $result = withPlaceholder($route, '{id}');
        $this->assertSame('/users/{id}', $result);

        $result = withPlaceholder($route, '{id:[0-9]+}', true);
        $this->assertSame('/users[/{id:[0-9]+}]', $result);
    }

    public function testEmptyPlaceholder(): void
    {
        $route = '/users';
        $this->assertSame
        (
            '/users',
            withPlaceholder( $route , '')
        );
    }

    public function testNullPlaceholder(): void
    {
        $this->assertSame
        (
            '/users',
            withPlaceholder( '/users' )
        );
    }

    public function testJoinPathsIntegration(): void
    {
        $route = '/path/';
        $result = withPlaceholder($route, 'id');
        $this->assertSame('/path/{id}', $result); // joinPaths ne duplique pas le slash
    }
}
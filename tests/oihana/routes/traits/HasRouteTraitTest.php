<?php

namespace tests\oihana\routes\traits;

use oihana\routes\enums\RouteFlag;
use oihana\routes\traits\HasRouteTrait;
use PHPUnit\Framework\TestCase;

class HasRouteTraitTest extends TestCase
{
    /**
     * Mock class using the trait.
     */
    protected object $mock;

    protected function setUp(): void
    {
        $this->mock = new class
        {
            use HasRouteTrait;
        };
    }

    public function test_default_flags_are_true_by_default(): void
    {
        $this->mock->initializeFlags([]);
        foreach ($this->getAllFlags() as $flag)
        {
            $this->assertTrue($this->mock->{$flag}(), "Flag $flag should default to true");
        }
    }

    public function test_default_flag_can_disable_all(): void
    {
        $this->mock->initializeFlags([RouteFlag::DEFAULT_FLAG => false]);

        foreach ($this->getAllFlags() as $flag)
        {
            $this->assertFalse($this->mock->{$flag}(), "Flag $flag should be false when DEFAULT_FLAG=false");
        }
    }

    public function test_partial_initialization_overrides_some_flags(): void
    {
        $this->mock->initializeFlags([
            RouteFlag::DEFAULT_FLAG => false,
            RouteFlag::HAS_GET      => true,
            RouteFlag::HAS_POST     => true,
        ]);

        $this->assertTrue($this->mock->hasGet());
        $this->assertTrue($this->mock->hasPost());

        // all others should remain false
        foreach ($this->getAllFlags(['hasGet', 'hasPost']) as $flag) {
            $this->assertFalse($this->mock->{$flag}(), "Flag $flag should remain false");
        }
    }

    public function test_all_flags_can_be_individually_overridden(): void
    {
        $init =
        [
            RouteFlag::HAS_COUNT           => false,
            RouteFlag::HAS_DELETE          => true,
            RouteFlag::HAS_DELETE_MULTIPLE => false,
            RouteFlag::HAS_GET             => true,
            RouteFlag::HAS_LIST            => false,
            RouteFlag::HAS_PATCH           => true,
            RouteFlag::HAS_POST            => false,
            RouteFlag::HAS_PUT             => true,
        ];

        $this->mock->initializeFlags($init);

        foreach ( $init as $const => $expected )
        {
            // Map constant to method name (lower camel case)
            $method = lcfirst(str_replace('HAS_', 'has', ucwords(strtolower(str_replace('_', ' ', $const)))));
            $this->assertSame( $expected , $this->mock->{$method}(), "Flag $const mismatch");
        }
    }

    public function test_enable_and_disable_flags(): void
    {
        $this->mock->initializeFlags([]);

        // Initially all flags true
        $this->assertTrue($this->mock->hasGet());
        $this->assertTrue($this->mock->hasPost());

        // Disable HAS_GET and HAS_POST
        $this->mock->disableFlags(RouteFlag::GET | RouteFlag::POST );
        $this->assertFalse($this->mock->hasGet());
        $this->assertFalse($this->mock->hasPost());

        // Enable HAS_GET again
        $this->mock->enableFlags(RouteFlag::GET );
        $this->assertTrue($this->mock->hasGet());
        $this->assertFalse($this->mock->hasPost());
    }

    public function test_describe_flags_returns_human_readable_string(): void
    {
        $this->mock->initializeFlags([
            RouteFlag::HAS_GET => true,
            RouteFlag::HAS_POST => false,
            RouteFlag::HAS_PUT => true,
        ]);

        $description = $this->mock->describeFlags();
        $this->assertIsString($description);
        $this->assertStringContainsString('GET', $description);
        $this->assertStringContainsString('PUT', $description);
        $this->assertStringNotContainsString('POST', $description);
    }

    public function test_initialize_with_integer(): void
    {
        $this->mock->initializeFlags(RouteFlag::READ_ONLY);

        $this->assertTrue($this->mock->hasGet());
        $this->assertTrue($this->mock->hasList());
        $this->assertTrue($this->mock->hasCount());
        $this->assertFalse($this->mock->hasPost());
    }

    public function test_describe_none_returns_NONE(): void
    {
        $this->mock->flags = RouteFlag::NONE ;
        $this->assertSame('NONE', $this->mock->describeFlags());
    }

    protected function getAllFlags(array $exclude = []): array
    {
        $flags =
        [
            'hasCount', 'hasDelete', 'hasDeleteMultiple',
            'hasGet', 'hasList', 'hasPatch', 'hasPost', 'hasPut',
        ];
        return array_values(array_diff($flags, $exclude));
    }


}
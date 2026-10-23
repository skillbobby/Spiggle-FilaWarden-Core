<?php

namespace Spiggle\FilaWarden\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Spiggle\FilaWarden\Security\AdvisoryVersionMatcher;

class AdvisoryVersionMatcherTest extends TestCase
{
    private AdvisoryVersionMatcher $matcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->matcher = new AdvisoryVersionMatcher;
    }

    public function test_php_8_5_4_matches_the_published_php2shell_range(): void
    {
        $this->assertTrue($this->matcher->affected('8.5.4', '< 8.5.12'));
        $this->assertFalse($this->matcher->affected('8.5.4', '< 8.2.35'));
        $this->assertFalse($this->matcher->affected('8.5.12', '< 8.5.12'));
        $this->assertTrue($this->matcher->sameBranch('8.5.4', '8.5.12'));
        $this->assertFalse($this->matcher->sameBranch('8.1.33', '8.2.35'));
    }

    public function test_packagist_or_groups_match_only_the_affected_band(): void
    {
        $constraint = '>=5.4.1,<5.4.54|>=7.4.0,<7.4.19|>=8.1.0,<8.1.7';

        $this->assertTrue($this->matcher->affected('7.4.10', $constraint));
        $this->assertFalse($this->matcher->affected('7.4.19', $constraint));
        $this->assertFalse($this->matcher->affected('v13.34.0', $constraint));
    }
}

<?php

use PHPUnit\Framework\TestCase;
use IngenicoClient\Onboarding;

/**
 * Class OnboardingTest.
 */
class OnboardingTest extends TestCase
{
    /**
     * @covers \IngenicoClient\Onboarding::getOnboardingEmailsByCountry
     *
     * @throws Exception
     */
    public function testGetOnboardingEmailsByCountry()
    {
        $onboarding = new Onboarding();

        $this->assertCount(1, $onboarding->getOnboardingEmailsByCountry('BE'));
        $this->assertCount(0, $onboarding->getOnboardingEmailsByCountry('RU'));
    }
}

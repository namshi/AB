<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 2/9/17
 * Time: 8:49 AM
 */

namespace Namshi\AB\Test;


use Namshi\AB\GaExperimentTrait;


class GaExperimentTraitTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @return GaExperimentTraitTest|Object
     */
    public function getTrait()
    {
        return $this->getObjectForTrait(GaExperimentTrait::class);
    }

    public function testGaExperimentIdAttribute()
    {
        $mock = $this->getTrait();
        $mock::setGoogleAnalyticsExperimentId('abc');
        $this->assertEquals('abc', $mock::getGoogleAnalyticsExperimentId());
    }
    public function testGaExperimentVariantAttribute()
    {
        $mock = $this->getTrait();
        $mock::setGoogleAnalyticsExperimentVariant('5');
        $this->assertEquals('5', $mock::getGoogleAnalyticsExperimentVariant());
    }
    public function testGaExperimentJs()
    {
        $mock = $this->getTrait();
        $mock::setGoogleAnalyticsExperimentId('abc');
        $mock::setGoogleAnalyticsExperimentVariant('5');
        $this->assertEquals(
            "ga('set', 'expId', 'abc'); ga('set', 'expVar', '5'); ga('send','pageview');",
            $mock::getGoogleAnalyticsExperimentJsContent()
        );
    }
    public function testGaExperimentJsWithNoSetup()
    {
        $mock = $this->getTrait();
        $this->assertEquals(
            "",
            $mock::getGoogleAnalyticsExperimentJsContent()
        );
        $mock::setGoogleAnalyticsExperimentId('abc');
        $this->assertEquals(
            "",
            $mock::getGoogleAnalyticsExperimentJsContent()
        );
        $mock::setGoogleAnalyticsExperimentId('');
        $mock::setGoogleAnalyticsExperimentVariant('5');
        $this->assertEquals(
            "",
            $mock::getGoogleAnalyticsExperimentJsContent()
        );
    }
}

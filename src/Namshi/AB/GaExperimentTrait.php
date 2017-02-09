<?php
/**
 * Created by PhpStorm.
 * User: andrew
 * Date: 2/9/17
 * Time: 8:37 AM
 */

namespace Namshi\AB;

/**
 * Trait GaExperimentTrait
 *
 * Set the google experiment Id by using a parameter with a 'expId' key.
 * Set google experiment variant id's by using parameters values with key names equal to the AbTest variant keys
 * See https://developers.google.com/analytics/solutions/experiments-server-side#store-user for how to determine what
 * the variation id's should be set to
 *
 * See https://developers.google.com/analytics/solutions/experiments-server-side for how to set up a server-side
 * experiment through google analytics
 *
 * @package Namshi\AB
 */
trait GaExperimentTrait
{

    private static $googleAnalyticsExperimentId = '';
    private static $googleAnalyticsExperimentVariant = '';


    /**
     * @return string
     */
    public static function getGoogleAnalyticsExperimentId()
    {
        return static::$googleAnalyticsExperimentId;
    }

    /**
     * @param string $googleAnalyticsExperimentId
     */
    public static function setGoogleAnalyticsExperimentId($googleAnalyticsExperimentId)
    {
        static::$googleAnalyticsExperimentId = $googleAnalyticsExperimentId;
    }

    /**
     * @return string
     */
    public static function getGoogleAnalyticsExperimentVariant()
    {
        return static::$googleAnalyticsExperimentVariant;
    }

    /**
     * @param string $googleAnalyticsExperimentVariant
     */
    public static function setGoogleAnalyticsExperimentVariant($googleAnalyticsExperimentVariant)
    {
        static::$googleAnalyticsExperimentVariant = $googleAnalyticsExperimentVariant;
    }


    /**
     * Returns all Javascript needed to send a server-side experiment to google analytics
     * Only dump this js data onto the page after analytics/tag manager has been initialized
     *
     * See https://developers.google.com/analytics/devguides/collection/analyticsjs/experiments#pro-server for more info
     * See https://developers.google.com/analytics/devguides/collection/analyticsjs/experiments#server-example for example
     * See https://developers.google.com/analytics/devguides/collection/analyticsjs/field-reference#expId for API info
     *
     * @return string
     */
    public static function getGoogleAnalyticsExperimentJsContent()
    {
        if( static::getGoogleAnalyticsExperimentId() !== ''
            && static::getGoogleAnalyticsExperimentVariant() !== ''
        ) {
            return "ga('set', 'expId', '".static::getGoogleAnalyticsExperimentId()."'); ga('set', 'expVar', '".static::getGoogleAnalyticsExperimentVariant()."'); ga('send','pageview');";
        }

        return '';
    }

}
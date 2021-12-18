<?php

namespace Pion\Metno;

use Pion\Metno\Contract\MetnoInterface;
use SimpleXMLElement;

/**
 * @author Martin Kluska @ iMakers, s.r.o. <martin.kluska@imakers.cz>
 * @copyright iMakers, s.r.o.
 * @copyright Martin Kluska
 * @web https://imakers.cz
 *
 */
class MetnoFactory implements MetnoInterface
{
    /**
     * class name for extending function for symbols. Sub class must extend
     * 
     * @var MetnoSymbol
     */
    static protected $classSymbol = "MetnoSymbol";

    /**
     * class name for precipitation wich must subclass <MetnoPrecipitation>
     * @var MetnoPrecipitation
     */
    static protected $classPrecipitation = "MetnoPrecipitation";

    /**
     * Defines hour wich will be used when selecting day forecast
     * - used only if <$dayForecastHighest> is false
     * - range of 0 - 23
     * 
     * @var int 
     */
    static protected int $dayForecastByHour = 14;

    /**
     * Defines hour wich will be used when selecting night forecast
     * - used only if <$nightForecastLowest> is false
     * - range of 0 - 23
     * 
     * @var int 
     */
    static protected int $nightForecastByHour = 23;

    /**
     * Defines hour wich defines when the night starts
     * - used only if <$nightForecastLowest> is true
     * - range of 0 - 23
     * 
     * @var int 
     */
    static protected int $nightHourStart = 20;

    /**
     * Weather forecast for all day is selected from the highest
     * temperature
     * 
     * @var bool 
     */
    static protected bool $dayForecastHighest = true;

    /**
     * Weather forecast for night is selected from the lowest temperature
     * 
     * @var bool 
     */
    static protected bool $nightForecastLowest = true;

    /**
     * Folder for caching by location and hour
     * 
     * @var string 
     */
    static protected string $cacheDir = "_METcache/";

    /**
     * Count of decimals for rounding of wind speed
     * 
     * @var int 
     */
    static protected int $decimalWindSpeed = 2;

    /**
     * Count of decimals for rounding of percente values
     * 
     * @var int 
     */
    static protected int $decimalPercente = 0;

    /**
     * Count of decimals for rounding of temperature
     * 
     * @var int 
     */
    static protected int $decimalTemperature = 0;

    /**
     * Display error and stop php
     * 
     * @var bool 
     */
    static protected bool $dieOnError = false;

    /**
     * Display error and continue
     * 
     * @var bool 
     */
    static protected bool $displayErrors = false;

    /**
     * Display error and stop php
     * 
     * @param bool $set
     * @return void
     */
    static public function setDieOnError(bool $set = true)
    {
        self::$dieOnError = $set;
    }

    /**
     * Display error and continue
     * 
     * @param bool $set
     * @return void
     */
    static public function setDisplayErrors(bool $set = true)
    {
        self::$displayErrors = $set;
    }

    /**
     * Sets the hour for selecting day forecast (disables selecting by highest
     * temperature)
     * 
     * @param int $hour
     * @return bool
     */
    static public function setHourForDayForecast(int $hour): bool
    {
        $hour = intval($hour);
        if ($hour >= 0 && $hour <= 23) {
            self::$dayForecastHighest = false;
            self::$dayForecastByHour = $hour;

            return true;
        }

        return false;
    }

    /**
     * Sets the hour for selecting night forecast (disables selecting by lowest
     * temperature)
     * 
     * @param int $hour
     * @return bool
     */
    static public function setHourForNightForecast(int $hour): bool
    {
        $hour = intval($hour);
        if ($hour >= 0 && $hour <= 23) {
            self::$nightForecastLowest = false;
            self::$nightForecastByHour = $hour;

            return true;
        }

        return false;
    }

    /**
     * Defines when the night starts
     * 
     * @param int $hour
     * @return bool
     */
    static public function setHourWhenNightStarts(int $hour): bool
    {
        $hour = intval($hour);
        if ($hour >= 0 && $hour <= 23) {
            self::$nightHourStart = $hour;

            return true;
        }

        return false;
    }

    /**
     * Sets if the day forecast should be choosed by highest temperature or
     * defined hour <MetnoFactory::setHourForDayForecast()>
     * 
     * @param bool $set
     * @return bool
     */
    static public function setDetectDayForecastByTemperature(bool $set = true): bool
    {
        self::$dayForecastHighest = $set;

        return true;
    }

    /**
     * Sets if the night forecast should be choosed by lowest temperature or
     * defined hour <MetnoFactory::setHourForNightForecast()>
     * 
     * @param bool $set
     * @return bool
     */
    static public function setDetectNightForecastByTemperature(bool $set = true): bool
    {
        self::$nightForecastLowest = $set;

        return true;
    }

    /**
     * Sets a class name for symbol. Must be subclass of <MetnoSymbol>
     *
     * - the class must exists or it tries to load it in the same directory
     * 
     * @param string $class_name
     * @return bool
     */
    static public function setSymbolClass(string $class_name): bool
    {
        self::loadClass($class_name); // try to load class if not present
        if (class_exists($class_name)) {
            self::$classSymbol = $class_name;

            return true;
        }

        return false;
    }

    /**
     * Sets a class name for precipitation. Must be subclass of
     * <MetnoPrecipitation>
     *
     * - the class must exists or it tries to load it in the same directory
     * 
     * @param $class_name
     * @return bool
     */
    static public function setPrecipitationClass($class_name): bool
    {
        if (!class_exists($class_name) && file_exists($class_name . ".php")) {
            require_once $class_name . ".php";
        }
        if (class_exists($class_name)) {
            self::$classPrecipitation = $class_name;

            return true;
        }

        return false;
    }

    /**
     * @param $class_name
     * @return void
     */
    static protected function loadClass($class_name)
    {
        if (!class_exists($class_name)) {
            if (file_exists(__DIR__ . "/" . $class_name . ".php")) {
                require_once __DIR__ . "/" . $class_name . ".php";
            } else {
                if (file_exists($class_name . ".php")) {
                    require_once $class_name . ".php";
                }
            }
        }
    }

    /**
     * Returns the number of decimals for windSpeed
     * @return boolean
     */
    static public function setWindSpeedDecimals($set)
    {
        self::$decimalWindSpeed = $set;

        return true;
    }

    /**
     * Returns the number of decimals for percente values
     * @return boolean
     */
    static public function setPercenteDecimals($set)
    {
        self::$decimalPercente = $set;

        return true;
    }

    /**
     * Returns the number of decimals for temperature
     * @return boolean
     */
    static public function setTemperatureDecimals($set)
    {
        self::$decimalTemperature = $set;

        return true;
    }

    /**
     * Gets forecast for location defined by Lat and Lon
     * 
     * @param float $lat
     * @param float $lon
     * @param int|null $seaLevel
     * @return Metno
     */
    static public function getForecastByLatLon(float $lat, float $lon, ?int $seaLevel = null): Metno
    {
        $yr = new Metno($lat, $lon, $seaLevel);
        $yr->getForecast();

        return $yr;
    }

    /**
     * Gets forecast for the location defined by the adress using google
     *
     * @param string $locationName
     * @return Metno
     */
    static public function getForecastByLocation(string $locationName): Metno
    {
        $lat = 0;
        $lon = 0;
        $yr = new Metno($lat, $lon);
        $yr->getForecast();

        return $yr;
    }

    /**
     * Returns only date 2012-08-27 from 2012-08-27T18:00:00Z
     *
     * @param $date
     * @return mixed
     */
    static public function getDate($date): mixed
    {
        if (preg_match("~([\d]{4})-([\d]{2})-([\d]{2})~", $date, $match)) {
            return $match[0];
        }

        return false;
    }

    /**
     * Returns only time 18:00 from 2012-08-27T18:00:00Z
     * 
     * @param $date
     * @return false|string
     */
    static public function getTime($date): bool|string
    {
        if (preg_match("~[\d]{4}-[\d]{2}-[\d]{2}T([\d]{2}):([\d]{2})~", $date, $match)
            && isset($match[1])
            && isset($match[2])) {
            return $match[1] . ":" . $match[2];
        }

        return false;
    }

    /**
     * Returns only hour 18 from 2012-08-27T18:00:00Z
     * 
     * @param $date
     * @return false|int
     */
    static public function getHour($date): bool|int
    {
        if (preg_match("~[\d]{4}-[\d]{2}-[\d]{2}T([\d]{2}):[\d]{2}~", $date, $match)
            && isset($match[1])) {
            return intval($match[1]);
        }

        return false;
    }

    /**
     * Checks in attributes array if there is an attribute key and returns
     * string or float with defined decimals
     * 
     * @param SimpleXMLElement $attributes
     * @param $attributeKey
     * @param int $floatValAndRoundByDecimals
     * @return false|float|int
     */
    static public function getAttributeValue(
        SimpleXMLElement $attributes,
        $attributeKey,
        int $floatValAndRoundByDecimals = -1
    ): float|bool|int {
        if (isset($attributes[$attributeKey])) {
            $value = $attributes[$attributeKey]->__toString();
            if ($floatValAndRoundByDecimals != -1) {
                $value = round(floatval($value), $floatValAndRoundByDecimals);
            }

            return $value;
        } else {
            if ($floatValAndRoundByDecimals != -1) {
                return 0;
            }
        }

        return false;
    }

    /**
     * Returns the number of decimals for windSpeed
     * 
     * @return int
     */
    static public function getWindSpeedDecimals(): int
    {
        return self::$decimalWindSpeed;
    }

    /**
     * Returns the number of decimals for percent values
     * 
     * @return int
     */
    static public function getPercentDecimals(): int
    {
        return self::$decimalPercente;
    }

    /**
     * Returns the number of decimals for temperature
     * 
     * @return int
     */
    static public function getTemperatureDecimals(): int
    {
        return self::$decimalTemperature;
    }

    /**
     * Get an entry of forecast by offset hour, first look in forecast Array
     * with hour - offset, if not set, find hour + offset, if not found,
     * increase offset +1 and start again. Max loops are 10, then boolean
     * returned
     * 
     * @param $hour
     * @param $forecastArrayByHour
     * @param int $offset
     * @return false
     */
    static public function getNearestForecastForHour(
        $hour,
        $forecastArrayByHour,
        int $offset = 1
    ): bool
    {
        $prevHour = $hour - $offset;
        $nextHour = $hour + $offset;
        if (isset($forecastArrayByHour[$nextHour])) {
            return $forecastArrayByHour[$nextHour];
        }
        if (isset($forecastArrayByHour[$prevHour])) {
            return $forecastArrayByHour[$prevHour];
        }
        if ($offset == 24) {
            return false;
        }

        return self::getNearestForecastForHour($hour, $forecastArrayByHour, $offset
            + 1);
    }

    /**
     * Get hour for day forecast
     * 
     * @return int
     */
    static public function getHourForDayForecast()
    {
        return self::$dayForecastByHour;
    }

    /**
     * Returns hour for night forecast
     * 
     * @return int
     */
    static public function getHourForNightForecast(): int
    {
        return self::$nightForecastByHour;
    }

    /**
     * Gets the hour when night starts
     * @return int
     */
    static public function getHourWhenNightStarts(): int
    {
        return self::$nightHourStart;
    }

    /**
     * Should the day info from the highest temperature?
     * 
     * @return bool
     */
    static public function isDayForecastByHighestTemp(): bool
    {
        return self::$dayForecastHighest;
    }

    /**
     * Should the night info from the lowest temperature?
     * Night detection is defined by the hour wich the night starts
     * <MetnoFactory::getHourWhenNightStarts()>
     * 
     * @return bool
     */
    static public function isNightForecastByLowestTemp(): bool
    {
        return self::$nightForecastLowest;
    }

}

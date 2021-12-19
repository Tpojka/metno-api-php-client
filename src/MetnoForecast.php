<?php

namespace Pion\Metno;

use SimpleXMLElement;

/**
 * @author Martin Kluska @ iMakers, s.r.o. <martin.kluska@imakers.cz>
 * @copyright iMakers, s.r.o.
 * @copyright Martin Kluska
 * @web https://imakers.cz
 * 
 */

class MetnoForecast
{
    /**
     * @var MetnoDay 
     */
    private $parent = false;
    
    /**
     *
     * @var MetnoSymbol 
     */
    protected $symbol = 0;

    /**
     * @var string 
     */
    protected string $date = "";

    /**
     * @var string 
     */
    protected string $hour = "";

    /**
     * Temperature in celcius
     * 
     * @var bool|int|string 
     */
    protected $temperature = 0;

    /**
     * Wind speed in m/s
     * 
     * @var bool|int|string 
     */
    protected $windSpeed = 0;

    /**
     * @var bool|int|string 
     */
    protected $windDegrees = 0;

    /**
     * @var bool|int|string 
     */
    protected $windOrientation = "NONE";

    /**
     * Precipitation (srážky) in mm
     * 
     * @var int|mixed 
     */
    protected $precipitation = 0;

    /**
     * @var array 
     */
    protected array $precipitationInHours = [];

    /**
     * Humidity (vlhkost) in percente
     * @var int 
     */
    protected $humidity = 0;

    /**
     * Pressure in hPa (default)
     * 
     * @var bool|int|string 
     */
    protected $pressure = 0;

    /**
     * @var bool|int|string 
     */
    protected $pressureUnit = "hPa";

    /**
     * Fog in percent
     * 
     * @var bool|int|string 
     */
    protected $fog = 0;

    /**
     * @var bool|int|string 
     */
    protected $cloudiness = 0;

    /**
     * @var bool|int|string 
     */
    protected $lowClouds = 0;

    /**
     * @var bool|int|string 
     */
    protected $mediumClouds = 0;

    /**
     * @var bool|int|string 
     */
    protected $highClouds = 0;

    /**
     * @param MetnoDay $parent
     * @param $date
     * @param $hour
     * @param SimpleXMLElement $mainXMLElement
     * @param $symbolsArray
     */
    public function __construct(
        MetnoDay $parent, 
        $date, 
        $hour, 
        SimpleXMLElement $mainXMLElement,
        $symbolsArray
    ) {
        $this->parent = $parent;
        $this->date = $date;
        $this->hour = $hour;        
        
        /**
         * Get all the data from main XML element - weather info (detail)
         */
        if (isset($mainXMLElement->temperature)) {
            $this->temperature = MetnoFactory::getAttributeValue($mainXMLElement->temperature->attributes(), 'value',  MetnoFactory::getTemperatureDecimals());
            
            MetnoFactory::getAttributeValue($mainXMLElement->temperature->attributes(), 'value', MetnoFactory::getTemperatureDecimals());
        }
        
        if (isset($mainXMLElement->windSpeed)) {
            $this->windSpeed = MetnoFactory::getAttributeValue($mainXMLElement->windSpeed->attributes(), 'value',  MetnoFactory::getWindSpeedDecimals());
        }
        
        if (isset($mainXMLElement->windDirection)) {
            $attribtues             = $mainXMLElement->windDirection->attributes();
            $this->windDegrees      = MetnoFactory::getAttributeValue($attribtues, 'deg',0);
            $this->windOrientation  = MetnoFactory::getAttributeValue($attribtues, 'name');
        }
        
        if (isset($mainXMLElement->humidity)) {
            $this->humidity         = MetnoFactory::getAttributeValue($mainXMLElement->humidity->attributes(), 'value',  MetnoFactory::getPercentDecimals());
        }
        
        if (isset($mainXMLElement->pressure)) {
            $attribtues             = $mainXMLElement->pressure->attributes();
            $this->pressure         = MetnoFactory::getAttributeValue($attribtues, 'value',1);
            $this->pressureUnit     = MetnoFactory::getAttributeValue($attribtues, 'unit');
        }
        
        if (isset($mainXMLElement->cloudiness)) {
            $this->cloudiness       = MetnoFactory::getAttributeValue($mainXMLElement->cloudiness->attributes(), 'percent',  MetnoFactory::getPercentDecimals());
        }
        
        if (isset($mainXMLElement->fog)) {
            $this->fog              = MetnoFactory::getAttributeValue($mainXMLElement->fog->attributes(), 'percent',  MetnoFactory::getPercentDecimals());
        }
        
        if (isset($mainXMLElement->lowClouds)) {
            $this->lowClouds        = MetnoFactory::getAttributeValue($mainXMLElement->lowClouds->attributes(), 'percent',  MetnoFactory::getPercentDecimals());
        }
        
        if (isset($mainXMLElement->mediumClouds)) {
            $this->mediumClouds     = MetnoFactory::getAttributeValue($mainXMLElement->mediumClouds->attributes(), 'percent',  MetnoFactory::getPercentDecimals());
        }
        
        if (isset($mainXMLElement->highClouds)) {
            $this->highClouds       = MetnoFactory::getAttributeValue($mainXMLElement->highClouds->attributes(), 'percent',  MetnoFactory::getPercentDecimals());
        }
        
        /**
         * Select symbol and precipitation from the nearest record and prepare
         * stats by 2 hours, 3 hours, 6 hours (difference)
         * @uses MetnoSymbol
         */
        
        if (!empty($symbolsArray)) {
            $first  = true;
            foreach ($symbolsArray as $symbol) {
                
                if ($first) {
                    $this->precipitation    = $symbol['precipitation'];
                    $this->symbol           = $symbol['symbol']->setWeather($this);                    
                    $first                  = false;
                } else {
                    $this->precipitationInHours[$symbol['difference']]  = $symbol['precipitation'];
                }                
            }
        }  
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->temperature;
    }

    /**
     * @return bool
     */
    public function isNight(): bool
    {
        return $this->hour >= MetnoFactory::getHourForNightForecast();
    }
    
    /**
     * @return MetnoDay
     */
    public function getMODay(): MetnoDay
    {
        return $this->parent;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getTime(): string
    {
        return $this->hour.":00";
    }

    /**
     * @return string
     */
    public function getHour(): string
    {
        return $this->hour;
    }

    /**
     * @return bool|int|string
     */
    public function getTemperature()
    {
        return $this->temperature;
    }

    /**
     * Returns the symbol for the weather
     * @return MetnoSymbol|int
     */
    public function getSymbol()
    {
        return $this->symbol;
    }
}

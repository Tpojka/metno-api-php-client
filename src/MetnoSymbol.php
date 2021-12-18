<?php

namespace Pion\Metno;

/**
 * @author Martin Kluska @ iMakers, s.r.o. <martin.kluska@imakers.cz>
 * @copyright iMakers, s.r.o.
 * @copyright Martin Kluska
 * @web https://imakers.cz
 * 
 * 
 * @uses METnoForecast Description
 */

class MetnoSymbol
{
    /**
     * For detection of day progress (night)
     * @var MetnoForecast 
     */
    protected MetnoForecast $weather;

    /**
     * @var int 
     */
    protected int $number = 1;

    /**
     * @var string 
     */
    protected string $name = "NONE";

    /**
     * @var string 
     */
    protected string $imageUrl     = "https://api.met.no/weatherapi/weathericon/1.1/?symbol={code};content_type=image/png";

    /**
     * @param int $number
     * @param string $name
     */
    public function __construct(int $number, string $name)
    {
        $this->number = $number;
        $this->name = $name;
    }

    /**
     * @param METnoForecast $weather
     * @return $this
     */
    public function setWeather(MetnoForecast $weather): MetnoSymbol
    {
        $this->weather = $weather;
        return $this;
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array|string|string[]
     */
    public function getUrl()
    {
        $url    = str_replace("{code}",$this->number,$this->imageUrl);
        /**
         * Detects if its night and show the right symbol
         */
        if ($this->isNight()) {
            $url.=";is_night=1";
        }
        
        return $url;
    }

    /**
     * @return bool
     */
    protected function isNight(): bool
    {
        return is_object($this->weather->getMODay()) && $this->weather->isNight();
    }

    /**
     * @return string
     */
    public function getHTML(): string
    {
        return "<img src='".$this->getUrl()."' alt='".$this->name."'/>";
    }

    /**
     * @return array|string|string[]
     */
    public function __toString()
    {
        return $this->getUrl();
    }
}

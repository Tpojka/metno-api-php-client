<?php

namespace Pion\Metno;

use JetBrains\PhpStorm\Pure;

/**
 * @author Martin Kluska @ iMakers, s.r.o. <martin.kluska@imakers.cz>
 * @copyright iMakers, s.r.o.
 * @copyright Martin Kluska
 * @web https://imakers.cz
 * 
 * 
 * Symbol documentation (string in lower case): 
 * @link http://api.met.no/weatherapi/weathericon/1.0/documentation
 */

class MetnoCustomSymbol extends MetnoSymbol
{
    /**
     * @var string 
     */
    static protected string $fileFormat = ".png";

    /**
     * Sets file format (extension) without dot
     * 
     * @param $fileFormat
     * @return void
     */
    static public function setFileFormat($fileFormat) {
        if ($fileFormat != "") {
            self::$fileFormat = ".$fileFormat";
        } else {
            self::$fileFormat = $fileFormat;
        }
    }

    /**
     * Returns global file format for icon
     * 
     * @return string
     */
    static public function getFileFormat(): string
    {
        return self::$fileFormat;
    }

    /**
     * Return url of the image with defined file format
     *
     * @link http://api.met.no/weatherapi/weathericon/1.0/documentation
     * @return string
     */
    #[Pure]
    public function getUrl(): string
    {
        return sprintf("%s-%s%s",
            $this->number,
            strtolower($this->name),
            MetnoCustomSymbol::getFileFormat()
        );
    }
}

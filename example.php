<?php

/**
 * @author Martin Kluska @ iMakers, s.r.o. <martin.kluska@imakers.cz>
 * @copyright iMakers, s.r.o.
 * @copyright Martin Kluska
 * @web https://imakers.cz
 * 
 * This software cannot be copied/edited or redistributed without permission by iMakers, s.r.o.
 */

use Pion\Metno\MetnoFactory;

if (!require_once 'vendor/autoload.php') {
    exit('Have you forgotten to run "composer install"?');
}

MetnoFactory::setHourForDayForecast(14);
MetnoFactory::setTemperatureDecimals(1);

$forecastBrno   = MetnoFactory::getForecastByLatLon(49.199205, 16.598866);

var_dump($forecastBrno);

// forecast in loop where you get desired days
// example using custom symbol in own directory
// same naming as the MET.no icons
// you need to set the custom symbol class (or create own)

//MetnoFactory::setSymbolClass("MetnoCustomSymbol");
//
//$forecastBrnoCustom   = MetnoFactory::getForecastByLatLon(49.199205, 16.598866);

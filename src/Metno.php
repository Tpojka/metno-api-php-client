<?php

namespace Pion\Metno;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Pion\Metno\Contract\MetnoInterface;

/**
 * @author Martin Kluska @ iMakers, s.r.o. <martin.kluska@imakers.cz>
 * @copyright iMakers, s.r.o.
 * @copyright Martin Kluska
 * @web https://imakers.cz
 *
 * @todo info about location, should be added to MetnoDay too
 * @todo time by location
 * @todo detection of night by sunset
 */
class Metno extends MetnoFactory
{
    /**
     * @var string 
     */
    protected string $apiRequest = "https://api.met.no/weatherapi/locationforecast/2.0/compact?";

    /**
     * @var string 
     */
    protected string $apiParameters = "";

    /**
     * If error has occurred, the exception object is saved here
     * 
     * @var bool|Exception 
     */
    protected $error = false;

    /**
     * @var string 
     */
    protected string $errorHTML = "";

    /**
     * @var array 
     */
    protected array $forecastByDay = [];

    /**
     * Constructs Metno class with specified $lat $lon location and option seaLevel
     * 
     * @param float $lat
     * @param float $lon
     * @param int|null $seaLevel
     */
    public function __construct(float $lat, float $lon, ?int $seaLevel = null)
    {
        $lat = round($lat, 2);
        $lon = round($lon, 2);
        
        $this->apiParameters .= sprintf("lat=%s&lon=%s",
            $lat,
            $lon
        );
        
        if (!is_null($seaLevel)) {
            $this->apiParameters .= '&altitude=' . $seaLevel;
        } 
    }

    /**
     * Sends Guzzle request and returns the content if no error
     * 
     * @param $url
     * @return false|string|void
     * @throws GuzzleException
     */
    protected function sendRequest($url)
    {
        try {
            $client = new Client();

            $response = $client->request('GET', $url, [
                'headers' => [
                    'User-Agent' => 'metno-api-php-client github.com/Tpojka',
                    'Accept'     => 'application/json'
                ]
            ]);

            return $response->getBody()->getContents();
            
        } catch (Exception $e) {
            return $this->error($e);
        }
    }

    /**
     * Genereates HTML Error and displays it if die on error is active
     * If die on error is not active, the html and Exception is saved for
     * internal use
     * 
     * @param Exception $e
     * @return false|void
     */
    public function error(Exception $e)
    {
        $this->errorHTML = "<h2>Metno - An error has occurred</h2>";
        $this->errorHTML .= "<table>";
        $this->errorHTML .= "<tr><td style='width: 100px;padding-right: 10px;text-align:right;'>File </td><td> " . $e->getFile() . ":<strong>" . $e->getLine() . "</strong></td></tr>";
        $this->errorHTML .= "<tr><td style='padding-right: 10px;text-align:right;'>Code </td><td> " . $e->getCode() . "</td></tr>";
        $this->errorHTML .= "<tr><td style='padding-right: 10px;text-align:right;'>Message </td><td> " . $e->getMessage() . "</td></tr>";
        $this->errorHTML .= "</table><h3>Stack trace</h3>";

        foreach ($e->getTrace() as $trace) {
            $this->errorHTML        .="<p>";
            if (isset($trace["class"]) && $trace['class'] != '') {
                $this->errorHTML    .= $trace['class'];
                $this->errorHTML    .= '->';
            }

            $this->errorHTML        .= $trace['function'];
            $this->errorHTML        .= '(';
            if (!empty($trace["args"])) {
                $first  = true;

                foreach($trace["args"] as $argument) {
                    if (is_string($argument)) {
                        if ($first) {
                            $first  = false;
                        } else {
                            $this->errorHTML.=",";
                        }
                        $this->errorHTML.= $argument;
                    }
                }
            }
            $this->errorHTML        .= ');<br />';
        }
        $this->errorHTML .= "</table>";

        if (self::$dieOnError) {
            header("Content-type: text/html; charset=utf-8");
            die($this->errorHTML);
        } else {
            $this->error = $e;

            if (self::$displayErrors) {
                header("Content-type: text/html; charset=utf-8");
                echo $this->errorHTML;
            }
        }

        return false;
    }

    /**
     * Detects if there was an error during parsing xml
     * 
     * @return bool
     */
    public function isError(): bool
    {
        return is_object($this->error);
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return !is_object($this->error);
    }

    /**
     * Return today forecast wich can be printed/echo to get current temperature
     * 
     * @return bool|MetnoForecast
     * @throws Exception
     */
    public function today()
    {
        return $this->getForecastForDate(date("Y-m-d"));
    }

    /**
     * Return tomorrow's forecast wich can be printed/echo to get current temperature
     * 
     * @return bool|MetnoForecast
     * @throws Exception
     */
    public function tomorrow()
    {
        return $this->getForecastForDate(date("Y-m-d", strtotime("+1 DAY")));
    }

    /**
     * Return forecast in 2 days wich can be printed/echo to get current temperature
     * 
     * @return bool|MetnoForecast
     * @throws Exception
     */
    public function in2Days()
    {
        return $this->getForecastForDate(date("Y-m-d",  strtotime("+2 DAY")));
    }

    /**
     * Return forecast in 3 days wich can be printed/echo to get current temperature
     * 
     * @return bool|MetnoForecast
     * @throws Exception
     */
    public function in3Days()
    {
        return $this->getForecastForDate(date("Y-m-d",  strtotime("+3 DAY")));
    }

    /**
     * Return forecast in 4 days which can be printed/echo to get current temperature
     * 
     * @return bool|MetnoForecast
     * @throws Exception
     */
    public function in4Days()
    {
        return $this->getForecastForDate(date("Y-m-d",  strtotime("+4 DAY")));
    }

    /**
     * Return forecast in 5 days wich can be printed/echo to get current temperature
     * 
     * @return bool|MetnoForecast
     * @throws Exception
     */
    public function in5Days()
    {
        return $this->getForecastForDate(date("Y-m-d",  strtotime("+5 DAY")));
    }

    /**
     * @param $count
     * @return array
     */
    public function getForecastForXDays($count): array
    {
        $current    = 0;
        $forecast   = array();

        foreach ($this->forecastByDay as $date => $forecastForDay) {
            $forecast[$date] = $forecastForDay;
            $current++;
            if ($current == $count) {
                break;
            }
        }

        return $forecast;
    }

    /**
     * Returns Exception object if an error has occurred
     * 
     * @return bool|Exception
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Returns and generated HTML with error details
     * @return string
     */
    public function getErrorHTML(): string
    {
        return $this->errorHTML;
    }

    /**
     * Returns error message from <Exception> object
     * @return string
     */
    public function getErrorMesssage(): string
    {
        if ($this->isError()) {
            return $this->error->getMessage();
        } else {
            return "No error has occurred";
        }
    }

    /**
     * @return array|bool|string|void
     */
    public function getForecast()
    {
        if (!empty($this->forecastByDay)) {
            return $this->forecastByDay;
        }
        
        try {
            return $this->getForecastJson();
        } catch (Exception $e) {
            return $this->error($e);
        }
    }

    /**
     * @return bool|string|void
     */
    protected function getForecastJson()
    {
        try {
            $cacheSubFolder     = date("Ymd")."/";
            $cacheFileName      = self::$cacheDir.$cacheSubFolder.$this->apiParameters."-".date("H").".json"; // prepare name of cache file by hour

            if (file_exists($cacheFileName)) {
                return file_get_contents($cacheFileName);
            } else {

                $json = $this->sendRequest($this->apiRequest.$this->apiParameters); // send request to api

                if (!is_dir(self::$cacheDir)) { // cache folder is not created
                    mkdir(self::$cacheDir);
                }

                if (!is_dir(self::$cacheDir.$cacheSubFolder)) {
                    mkdir(self::$cacheDir.$cacheSubFolder);
                }

                /**
                 * Create hour cache file and delete previous cache file HOUR - 1
                 */
                $cache = fopen($cacheFileName, "w");

                fwrite($cache, $json);
                fclose($cache);

                // remove the previous hour
                $previousHour = date("H",strtotime("-1 HOUR"));

                $cacheFileOld = self::$cacheDir.$cacheSubFolder.$this->apiParameters."-".$previousHour.".json";

                if (file_exists($cacheFileOld)) {
                    @unlink($cacheFileOld);
                }

                // remove the previous day
                $cacheSubFolder     = date("Ymd",strtotime("-1 DAY"))."/";

                if (is_dir(self::$cacheDir.$cacheSubFolder)) {
                    $this->rmdirRecursively(self::$cacheDir.$cacheSubFolder);
                }
            }

            return $json;
        } catch (Exception $e) {
            return $this->error($e);
        }
    }

    /**
     * Removes all the contents of dir
     * 
     * @param string $dir
     * @return bool
     */
    public function rmdirRecursively(string $dir): bool
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->rmdirRecursively("$dir/$file") : @unlink("$dir/$file");
        }
        return @rmdir($dir);
    }

    /**
     * @param $date
     * @return mixed
     */
    public function getForecastForDate($date)
    {
        if (empty($this->forecastByDay)) {
            $this->getForecast();
        }

        if (isset($this->forecastByDay[$date])) {
            return $this->forecastByDay[$date];
        }

        return $this->error(new Exception("Forecast for date $date doesn't exist", MetnoInterface::DATA_EMPTY));
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $forecast = $this->getForecastJson();
        
        if (!is_string($forecast)) {
            $forecast = $this->error->getMessage();
        }
        
        return $forecast;
    }
}

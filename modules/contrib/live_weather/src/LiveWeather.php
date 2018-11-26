<?php
/**
 * @file
 * Contains \Drupal\live_weather\LiveWeather.
 */

namespace Drupal\live_weather;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Serialization\Json;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Drupal\Component\Utility\Unicode;

/**
 * Live weather.
 */
class LiveWeather implements LiveWeatherInterface {

  /**
   * The HTTP client to fetch the feed data with.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a location form object.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The Guzzle HTTP client.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   */
  public function __construct(ClientInterface $http_client, LoggerInterface $logger) {
    $this->httpClient = $http_client;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
      $container->get('logger.factory')
    );
  }

  /**
   * Get location data.
   */
  public function locationCheck($woeid = NULL, $filter = '', $unit = 'f') {
    $data = '';
    $url = 'https://query.yahooapis.com/v1/public/yql?q=select ' . $filter . ' from weather.forecast where woeid IN (' . $woeid . ') and u= "' . $unit . '"&format=json';
    try {
      $response = $this->httpClient->get($url);
      $data = (string) $response->getBody();
    }
    catch (RequestException $e) {
      $this->logger->warning('Failed to get data "%error".', array('%error' => $e->getMessage()));
      return;
    }catch (BadResponseException $e) {
      $this->logger->warning('Failed to get data "%error".', array('%error' => $e->getMessage()));
      return;
    }
    catch (\Exception $e) {
      $this->logger->warning('Failed to get data "%error".', array('%error' => $e->getMessage()));
      return;
    }
    if (!empty($data)) {
      $data = Json::decode($data);
    }
    return $data;
  }

  /**
   * Check Day or Night.
   */
  public static function checkDayNight($date, $sunrise, $sunset) {
    $position = Unicode::strpos($date, ":");
    $tpb = Unicode::substr($date, $position - 2, 8);
    $actual_time = strtotime($tpb);
    $sunrise_time = strtotime($sunrise);
    $sunset_time = strtotime($sunset);
    if ($actual_time > $sunrise_time && $actual_time < $sunset_time) {
      return 'd';
    }
    else {
      return 'n';
    }
    return 'd';
  }

  /**
   * Get Wind Direction.
   */
  public static function windDirection($direction) {
    if ($direction >= 348.75 && $direction <= 360) {
      $direction = "N";
    }
    elseif ($direction >= 0 && $direction < 11.25) {
      $direction = "N";
    }
    elseif ($direction >= 11.25 && $direction < 33.75) {
      $direction = "NNE";
    }
    elseif ($direction >= 33.75 && $direction < 56.25) {
      $direction = "NE";
    }
    elseif ($direction >= 56.25 && $direction < 78.75) {
      $direction = "ENE";
    }
    elseif ($direction >= 78.75 && $direction < 101.25) {
      $direction = "E";
    }
    elseif ($direction >= 101.25 && $direction < 123.75) {
      $direction = "ESE";
    }
    elseif ($direction >= 123.75 && $direction < 146.25) {
      $direction = "SE";
    }
    elseif ($direction >= 146.25 && $direction < 168.75) {
      $direction = "SSE";
    }
    elseif ($direction >= 168.75 && $direction < 191.25) {
      $direction = "S";
    }
    elseif ($direction >= 191.25 && $direction < 213.75) {
      $direction = "SSW";
    }
    elseif ($direction >= 213.75 && $direction < 236.25) {
      $direction = "SW";
    }
    elseif ($direction >= 236.25 && $direction < 258.75) {
      $direction = "WSW";
    }
    elseif ($direction >= 258.75 && $direction < 281.25) {
      $direction = "W";
    }
    elseif ($direction >= 281.25 && $direction < 303.75) {
      $direction = "WNW";
    }
    elseif ($direction >= 303.75 && $direction < 326.25) {
      $direction = "NW";
    }
    elseif ($direction >= 326.25 && $direction < 348.75) {
      $direction = "NNW";
    }
    return $direction;
  }

}

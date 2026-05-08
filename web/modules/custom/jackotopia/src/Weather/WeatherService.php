<?php

declare(strict_types=1);

namespace Drupal\jackotopia\Weather;

use GuzzleHttp\ClientInterface;

/**
 * Pulls the current weather for the Drupal Camp venue (Chicago, 60614).
 *
 * Backed by Open-Meteo (no API key required). The API endpoint is hardcoded
 * to the camp venue's lat/lon on purpose: we want the service surface to be
 * tiny and obvious so the test stays focused on testing a *service*, not on
 * geocoding or configuration plumbing.
 */
final class WeatherService {

  /**
   * Lincoln Park, Chicago — DePaul / Drupal Camp venue area (zip 60614).
   */
  private const LATITUDE = 41.9249;
  private const LONGITUDE = -87.6532;

  private const ENDPOINT = 'https://api.open-meteo.com/v1/forecast';

  public function __construct(
    private readonly ClientInterface $httpClient,
  ) {}

  /**
   * Returns the current weather snapshot for the venue.
   *
   * @return array{temperature_f: float, weather_code: int, time: string}
   */
  public function getCurrentConditions(): array {
    $response = $this->httpClient->request('GET', self::ENDPOINT, [
      'query' => [
        'latitude' => self::LATITUDE,
        'longitude' => self::LONGITUDE,
        'current' => 'temperature_2m,weather_code',
        'temperature_unit' => 'fahrenheit',
      ],
      'timeout' => 5,
    ]);

    $data = json_decode((string) $response->getBody(), TRUE, flags: JSON_THROW_ON_ERROR);

    return [
      'temperature_f' => (float) $data['current']['temperature_2m'],
      'weather_code' => (int) $data['current']['weather_code'],
      'time' => (string) $data['current']['time'],
    ];
  }

}

<?php

declare(strict_types=1);

namespace Drupal\jackotopia\Weather;

use GuzzleHttp\ClientInterface;

/**
 * Pulls current weather for a zip code, via Open-Meteo (no API key needed).
 *
 * The "zip → lat/lon" map is intentionally a tiny hardcoded array. This is a
 * teaching artifact, not a geocoder. Add zips here as the talk demands them.
 */
final class WeatherService {

  private const ENDPOINT = 'https://api.open-meteo.com/v1/forecast';

  /**
   * Known zip codes to (latitude, longitude) pairs.
   */
  private const COORDINATES = [
    // Lincoln Park, Chicago — Drupal Camp venue area.
    '60614' => [41.9249, -87.6532],
    // Jackson Township / Canton, OH.
    '44718' => [40.8569, -81.4229],
  ];

  public function __construct(
    private readonly ClientInterface $httpClient,
  ) {}

  /**
   * Returns the current weather snapshot for a known zip code.
   *
   * @return array{temperature_f: float, weather_code: int, time: string}
   *   Temperature in Fahrenheit, WMO weather code, and ISO 8601 timestamp.
   *
   * @throws \InvalidArgumentException
   *   If the zip code isn't in our hardcoded map.
   */
  public function getCurrentConditionsForZip(string $zip): array {
    if (!isset(self::COORDINATES[$zip])) {
      throw new \InvalidArgumentException(sprintf('Unknown zip code: %s', $zip));
    }
    [$latitude, $longitude] = self::COORDINATES[$zip];

    $response = $this->httpClient->request('GET', self::ENDPOINT, [
      'query' => [
        'latitude' => $latitude,
        'longitude' => $longitude,
        'current' => 'temperature_2m,weather_code',
        'temperature_unit' => 'fahrenheit',
      ],
      'timeout' => 5,
    ]);

    $data = json_decode((string) $response->getBody(), TRUE, flags: JSON_THROW_ON_ERROR);
    if (!is_array($data) || !isset($data['current']) || !is_array($data['current'])) {
      throw new \RuntimeException('Unexpected Open-Meteo response shape.');
    }
    $temperature = $data['current']['temperature_2m'] ?? NULL;
    $weatherCode = $data['current']['weather_code'] ?? NULL;
    $time = $data['current']['time'] ?? NULL;
    if (!is_numeric($temperature) || !is_numeric($weatherCode) || !is_string($time)) {
      throw new \RuntimeException('Unexpected Open-Meteo response shape.');
    }

    return [
      'temperature_f' => (float) $temperature,
      'weather_code' => (int) $weatherCode,
      'time' => $time,
    ];
  }

}

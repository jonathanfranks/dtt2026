<?php

namespace Drupal\Tests\jackotopia\ExistingSite;

use Drupal\jackotopia\Weather\WeatherService;

/**
 * Tests jackotopia.weather against the live Open-Meteo API.
 *
 * Yes, live. ExistingSiteBase tests run against a real Drupal site, so it's
 * also fine to let them reach a real network dependency — that's the point of
 * this kind of test, to prove integration end to end. The trade-off: if
 * Open-Meteo or the network is down, the test goes red. Worth it for the
 * confidence; mock it later if the dependency becomes flaky.
 */
class WeatherServiceTest extends JackotopiaTestBase {

  /**
   * Make sure our service is registered.
   */
  public function testServiceIsRegisteredOnTheContainer() {
    $service = \Drupal::service('jackotopia.weather');
    $this->assertInstanceOf(WeatherService::class, $service);
  }

  /**
   * Makes sure we get real results from the service.
   *
   * @throws \JsonException
   */
  public function testGetCurrentConditionsReturnsSomething() {
    /** @var \Drupal\jackotopia\Weather\WeatherService $weather */
    $weather = \Drupal::service('jackotopia.weather');
    $conditions = $weather->getCurrentConditionsForZip('60614');

    $this->assertSame(
      ['temperature_f', 'weather_code', 'time'],
      array_keys($conditions),
      'Service returns exactly the documented keys.',
    );

    // We don't assert today's actual temperature — it's always different.
    // We DO assert it's a number in a range that says "this is really Chicago
    // weather and not, say, an HTML error page parsed as -250."
    $this->assertIsFloat($conditions['temperature_f']);
    $this->assertGreaterThan(-40.0, $conditions['temperature_f']);
    $this->assertLessThan(130.0, $conditions['temperature_f']);

    // WMO weather codes are 0..99.
    $this->assertIsInt($conditions['weather_code']);
    $this->assertGreaterThanOrEqual(0, $conditions['weather_code']);
    $this->assertLessThanOrEqual(99, $conditions['weather_code']);

    // Open-Meteo returns ISO 8601 like "2026-05-08T22:15".
    $this->assertMatchesRegularExpression(
      '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/',
      $conditions['time'],
    );
  }

}

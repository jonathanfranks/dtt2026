<?php

namespace Drupal\Tests\jackotopia\Traits;

use Drupal\Core\Config\ImmutableConfig;

/**
 * Wraps some config into assertions into a trait for testing.
 */
trait ConfigTrait {

  /**
   * Get the site name.
   *
   * @return string|null
   *   The site name.
   */
  protected function getSiteName(): ?string {
    $name = \Drupal::configFactory()->get('system.site')->get('name');
    return is_string($name) ? $name : NULL;
  }

  /**
   * Read a config object.
   */
  protected function getConfig(string $name): ImmutableConfig {
    return \Drupal::configFactory()->get($name);
  }

  /**
   * Assert a value at a dotted key inside a config object.
   *
   * @param string $name
   *   Config object name (e.g. 'views.view.testable_view').
   * @param string $key
   *   Dotted key path (e.g. 'display.default.display_options.sorts').
   * @param mixed $expected
   *   The value the config is expected to hold at that key.
   */
  protected function assertConfigValue(string $name, string $key, mixed $expected): void {
    $this->assertSame(
      $expected,
      $this->getConfig($name)->get($key),
      sprintf('Config %s:%s did not match expected value.', $name, $key),
    );
  }

}

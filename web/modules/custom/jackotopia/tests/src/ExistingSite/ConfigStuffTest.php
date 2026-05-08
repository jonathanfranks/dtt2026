<?php

namespace Drupal\Tests\jackotopia\ExistingSite;

use Drupal\Tests\jackotopia\Traits\ConfigTrait;

/**
 * Tiny config-only assertions — no HTTP, no nodes, just ImmutableConfig reads.
 */
class ConfigStuffTest extends JackotopiaTestBase {

  use ConfigTrait;

  /**
   * Tests the site name.
   */
  public function testSiteName(): void {
    $expected = 'Drush Site-Install';
    $actual = $this->getSiteName();
    $this->assertEquals($expected, $actual);
  }

  /**
   * Visitor registration is locked down to admin-only.
   */
  public function testVisitorRegistrationIsAdminOnly(): void {
    $this->assertConfigValue('user.settings', 'register', 'admin_only');
  }

  /**
   * The default front-end theme is Olivero.
   */
  public function testDefaultThemeIsOlivero(): void {
    $this->assertConfigValue('system.theme', 'default', 'olivero');
  }

}

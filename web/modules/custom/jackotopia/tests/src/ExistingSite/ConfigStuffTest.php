<?php

namespace Drupal\Tests\jackotopia\ExistingSite;

use Drupal\Tests\jackotopia\Traits\ConfigTrait;

class ConfigStuffTest extends JackotopiaTestBase {

  use ConfigTrait;

  /**
   * Tests the site name.
   */
  public function testSiteName() {
    $expected = 'Drush Site-Install';
    $actual = $this->getSiteName();
    $this->assertEquals($expected, $actual);
  }

  /**
   * Visitor registration is locked down to admin-only.
   */
  public function testVisitorRegistrationIsAdminOnly() {
    $this->assertConfigValue('user.settings', 'register', 'admin_only');
  }

  /**
   * The default front-end theme is Olivero.
   */
  public function testDefaultThemeIsOlivero() {
    $this->assertConfigValue('system.theme', 'default', 'olivero');
  }

}

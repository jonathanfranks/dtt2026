<?php

namespace Drupal\Tests\jackotopia\CoreStuff;

use weitzman\DrupalTestTraits\ExistingSiteBase;

class CoreStuffTest extends ExistingSiteBase {

  /**
   * Tests that a user can log in.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testUserLogin() {
    $user = $this->createUser();
    $this->drupalLogin($user);
  }

}

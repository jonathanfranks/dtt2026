<?php

namespace Drupal\Tests\jackotopia\ExistingSite;

class CoreStuffTest extends JackotopiaTestBase {

  /**
   * Tests that a user can log in.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testUserLogin() {
    $user = $this->createUser();
    $this->drupalLogin($user);
  }

  /**
   * Tests that we can bypass the error checking and check the text on the page.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testErrorPage() {
    $user = $this->createUser();
    $this->drupalLogin($user);
    $this->drupalGet('/jackotopia/error-message', allowErrors: TRUE);

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('It works!');
    $this->assertSession()->pageTextContains('Uh oh, this looks like an error.');
  }

  /**
   * Tests the errors page.
   *
   * We expect this test to fail as it sits because there is a Drupal error
   * message displayed on this page.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testErrorAssertionsFail() {
    $user = $this->createUser();
    $this->drupalLogin($user);
    $this->drupalGet('/jackotopia/error-message');
  }

}

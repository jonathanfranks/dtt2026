<?php

declare(strict_types=1);

namespace Drupal\Tests\jackotopia\ExistingSite;

/**
 * Foundational DTT tests — login, and the auto-error guardrail demo.
 */
class CoreStuffTest extends JackotopiaTestBase {

  /**
   * Tests that a user can log in.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testUserLogin(): void {
    $user = $this->createUser();
    $this->assertNotFalse($user);
    $this->drupalLogin($user);
  }

  /**
   * Tests that we can bypass the error checking and check the text on the page.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testErrorPage(): void {
    $user = $this->createUser();
    $this->assertNotFalse($user);
    $this->drupalLogin($user);
    $this->drupalGet('/jackotopia/error-message', allow_errors: TRUE);

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('It works!');
    $this->assertSession()->pageTextContains('Uh oh, this looks like an error.');

    $this->assertPageTitle('Error Message');
  }

  /**
   * Tests the errors page.
   *
   * We expect this test to fail as it sits because there is a Drupal error
   * message displayed on this page. CI excludes this group so the badge stays
   * green; locally it fires every time, which is the demo.
   *
   * @group intentionally_failing
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testErrorAssertionsFail(): void {
    $user = $this->createUser();
    $this->assertNotFalse($user);
    $this->drupalLogin($user);
    $this->drupalGet('/jackotopia/error-message');
  }

}

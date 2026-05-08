<?php

namespace Drupal\Tests\jackotopia\ExistingSite;

/**
 * Two ways to assert that the Testable View sorts by title (not by created).
 */
class TestableViewTest extends JackotopiaTestBase {

  /**
   * Behavioural: render the view and check the row order in the page.
   */
  public function testViewIsSortedByTitle() {
    // Create three nodes whose creation order is the OPPOSITE of their
    // alphabetical order. If the view sorted by 'created' (newest first), the
    // page would show Alpha, Bravo, Charlie reversed — Charlie first.
    $this->createNode(['type' => 'article', 'title' => 'Charlie', 'status' => 1]);
    $this->createNode(['type' => 'article', 'title' => 'Bravo', 'status' => 1]);
    $this->createNode(['type' => 'article', 'title' => 'Alpha', 'status' => 1]);

    $this->drupalGet('/testable-view');

    $titles = array_map(
      fn($el) => trim($el->getText()),
      $this->getSession()->getPage()->findAll('css', '.views-field-title'),
    );
    // Drop any header cell that has empty text.
    $titles = array_values(array_filter($titles, fn($t) => $t !== '' && $t !== 'Title'));

    $this->assertSame(['Alpha', 'Bravo', 'Charlie'], $titles);
  }

  /**
   * Config-level: read the view config and assert its default sort.
   */
  public function testViewConfigSortsByTitle() {
    $sorts = $this->getConfig('views.view.testable_view')
      ->get('display.default.display_options.sorts');

    $this->assertSame(['title'], array_keys($sorts), 'Default display has exactly one sort, on title.');
    $this->assertConfigValue(
      'views.view.testable_view',
      'display.default.display_options.sorts.title.field',
      'title',
    );
    $this->assertConfigValue(
      'views.view.testable_view',
      'display.default.display_options.sorts.title.order',
      'ASC',
    );
  }

}

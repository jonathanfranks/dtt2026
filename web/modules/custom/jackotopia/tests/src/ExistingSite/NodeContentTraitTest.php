<?php

namespace Drupal\Tests\jackotopia\ExistingSite;

use Drupal\Tests\jackotopia\Traits\NodeContentTrait;

/**
 * Demos NodeContentTrait — perturb published-content state and restore it.
 *
 * The pattern: a test wants to assert "the empty state" (e.g. a view shows no
 * rows) without permanently nuking real content the rest of the suite needs.
 * unpublishNodesOfBundle() flips the bit and remembers what it touched;
 * republishUnpublishedNodes() in tearDown() puts everything back, even if
 * the assertions in the middle blow up.
 */
class NodeContentTraitTest extends JackotopiaTestBase {

  use NodeContentTrait;

  /**
   * {@inheritdoc}
   */
  protected function tearDown(): void {
    // Run BEFORE parent::tearDown() — DTT's tearDown deletes the test-created
    // nodes, and we need those still around to flip back to published.
    $this->republishUnpublishedNodes();
    parent::tearDown();
  }

  /**
   * Pagination should disappear when only one published node is left.
   *
   * /two-per-page is a view with a pager size of 2. It only renders a pager
   * when there's more than one page of results. To test the "one item, no
   * pager" branch reliably, we have to start from a known-empty state — but
   * we don't want to permanently delete real content. NodeContentTrait lets
   * us flip the bit, run the assertion, and put everything back in tearDown.
   */
  public function testPagerDisappearsBelowPageSize(): void {
    // A pair of "noise" articles. With these published, the view has more
    // than one page of results and the pager renders.
    $this->createNode(['type' => 'article', 'title' => 'Noise A', 'status' => 1]);
    $this->createNode(['type' => 'article', 'title' => 'Noise B', 'status' => 1]);
    $this->createNode(['type' => 'article', 'title' => 'Noise C', 'status' => 1]);

    $this->drupalGet('/two-per-page');
    $this->assertSession()->elementExists('css', 'nav.pager');

    // Wipe the slate. Trait remembers what it touched.
    $this->unpublishNodesOfBundle('article');

    // Add a single published article on top of the (now-empty) view.
    $this->createNode(['type' => 'article', 'title' => 'Lone article', 'status' => 1]);

    $this->drupalGet('/two-per-page');
    $this->assertSession()->pageTextContains('Lone article');
    $this->assertSession()->elementNotExists('css', 'nav.pager');
  }

}

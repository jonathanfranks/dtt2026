<?php

namespace Drupal\Tests\jackotopia\ExistingSite;

use Drupal\Tests\jackotopia\Traits\DebugTrait;
use weitzman\DrupalTestTraits\ExistingSiteBase;

class JackotopiaTestBase extends ExistingSiteBase {

  use DebugTrait;

  /**
   * Follows a link by complete name.
   *
   * Will click the first link found with this link text.
   *
   * If the link is discovered and clicked, the test passes. Fail otherwise.
   *
   * @param string|\Drupal\Component\Render\MarkupInterface $label
   *   Text between the anchor tags.
   * @param int $index
   *   (optional) The index number for cases where multiple links have the same
   *   text. Defaults to 0.
   * @param array $texts
   *   Array of error message to check. Blank for defaults.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function clickLinkWithExplicitErrorMessageCheck(
    $label,
    int $index = 0,
    array $texts = [],
  ) {
    parent::clickLink($label, $index);
    $this->assertNoErrorMessage($texts);
  }

  /**
   * {@inheritdoc}
   */
  public function click($css_selector, bool $allowErrors = FALSE) {
    parent::click($css_selector);
    if (!$allowErrors) {
      $this->assertNoErrorMessage();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function drupalGet($path, array $options = [], array $headers = [], bool $allowErrors = FALSE) {
    // Tell BigPipe to render lazy-builder placeholders inline (server-side)
    // instead of streaming them via <script type="application/vnd.drupal-ajax">
    // chunks that only execute under a real JS browser. Without this, content
    // like flash messages is invisible to BrowserKit/Goutte-driven assertions.
    $this->getSession()->setCookie('big_pipe_nojs', '1');

    $return = parent::drupalGet($path, $options, $headers);
    if (!$allowErrors) {
      $this->assertNoErrorMessage();
    }
    return $return;
  }

  /**
   * Asserts that there are no error messages on the page.
   *
   * @param array $texts
   *   Assert page does not contain texts in array values. Defaults to
   *   'The website encountered an unexpected error.', 'Notice:', and
   *   'Warning:'.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function assertNoErrorMessage(array $texts = []) {
    if (empty($texts)) {
      $texts = [
        'The website encountered an unexpected error.',
        'Notice:',
        'Warning:',
      ];
    }
    foreach ($texts as $text) {
      $this->assertSession()->pageTextNotContains($text);
    }
    $this->assertSession()->elementNotExists('css', '.messages--error, .alert-danger');
  }


}

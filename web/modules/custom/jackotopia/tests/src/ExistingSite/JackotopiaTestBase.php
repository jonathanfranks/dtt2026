<?php

declare(strict_types=1);

namespace Drupal\Tests\jackotopia\ExistingSite;

use Drupal\Tests\jackotopia\Traits\ConfigTrait;
use Drupal\Tests\jackotopia\Traits\DebugTrait;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Base test class for jackotopia ExistingSite tests.
 *
 * Adds two pieces of free plumbing every subclass gets: an auto-error check
 * after every drupalGet/click, and a BigPipe no-JS cookie so lazy-built
 * content (like flash messages) is actually present in the response.
 */
class JackotopiaTestBase extends ExistingSiteBase {

  use ConfigTrait;
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
   * @param string[] $texts
   *   Array of error message to check. Blank for defaults.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function clickLinkWithExplicitErrorMessageCheck(
    $label,
    int $index = 0,
    array $texts = [],
  ): void {
    parent::clickLink($label, $index);
    $this->assertNoErrorMessage($texts);
  }

  /**
   * {@inheritdoc}
   *
   * @param string $css_selector
   *   CSS selector identifying the element to click.
   * @param bool $allow_errors
   *   When TRUE, skip the auto-error check after the click.
   */
  public function click($css_selector, bool $allow_errors = FALSE): void {
    parent::click($css_selector);
    if (!$allow_errors) {
      $this->assertNoErrorMessage();
    }
  }

  /**
   * {@inheritdoc}
   *
   * @param string|\Drupal\Core\Url $path
   *   Drupal path or URL to load.
   * @param array<string, mixed> $options
   *   Options to forward to the URL generator.
   * @param string[] $headers
   *   Additional HTTP request headers.
   * @param bool $allow_errors
   *   When TRUE, skip the auto-error check after the request.
   *
   * @return string
   *   The retrieved HTML.
   */
  public function drupalGet($path, array $options = [], array $headers = [], bool $allow_errors = FALSE): string {
    // Tell BigPipe to render lazy-builder placeholders inline (server-side)
    // instead of streaming them via <script type="application/vnd.drupal-ajax">
    // chunks that only execute under a real JS browser. Without this, content
    // like flash messages is invisible to BrowserKit/Goutte-driven assertions.
    $this->getSession()->setCookie('big_pipe_nojs', '1');

    $return = parent::drupalGet($path, $options, $headers);
    if (!$allow_errors) {
      $this->assertNoErrorMessage();
    }
    return $return;
  }

  /**
   * Asserts that there are no error messages on the page.
   *
   * @param string[] $texts
   *   Assert page does not contain texts in array values. Defaults to
   *   'The website encountered an unexpected error.', 'Notice:', and
   *   'Warning:'.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function assertNoErrorMessage(array $texts = []): void {
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

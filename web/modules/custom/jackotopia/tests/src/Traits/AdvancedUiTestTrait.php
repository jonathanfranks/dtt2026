<?php

declare(strict_types=1);

namespace Drupal\Tests\jackotopia\Traits;

use Behat\Mink\Element\Element;
use Behat\Mink\Element\ElementInterface;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\ResponseTextException;

/**
 * Trait for more advanced UI automation.
 *
 * So far, these functions are lifted from our custom Drupal contexts or from
 * the default Behat Drupal context. I haven't put too much time into trying to
 * re-use existing Behat context classes and pass in a Mink variable.
 */
trait AdvancedUiTestTrait {

  /**
   * Attempts to press a button in a table row containing given text.
   */
  protected function assertPressInTableRow(string $button, string $rowText): void {
    $page = $this->getSession()->getPage();
    $row = $this->getTableRow($page, $rowText);
    if ($row->findButton($button)) {
      $row->pressButton($button);
      return;
    }
    throw new \Exception(sprintf('Found a row containing "%s", but no "%s" button on the page %s', $rowText, $button, $this->getSession()
      ->getCurrentUrl()));
  }

  /**
   * Attempts to find a link in a table row containing giving text.
   *
   * This is for administrative pages such as the administer content types
   * screen found at `admin/structure/types`.
   *
   * @Given I click :link in the :rowText row
   * @Then I (should )see the :link in the :rowText row
   */
  public function assertClickInTableRow(string $link, string $rowText): void {
    $page = $this->getSession()->getPage();
    if ($link_element = $this->getTableRow($page, $rowText)->findLink($link)) {
      $link_element->click();
      return;
    }
    throw new \Exception(sprintf('Found a row containing "%s", but no "%s" link on the page %s', $rowText, $link, $this->getSession()
      ->getCurrentUrl()));
  }

  /**
   * Retrieve a table row containing specified text from a given element.
   *
   * @param \Behat\Mink\Element\Element $element
   *   The element.
   * @param string $search
   *   The text to search for in the table row.
   *
   * @return \Behat\Mink\Element\NodeElement
   *   The node element.
   *
   * @throws \Exception
   */
  public function getTableRow(Element $element, string $search): NodeElement {
    $rows = $element->findAll('css', 'tr');
    if (empty($rows)) {
      throw new \Exception(sprintf('No rows found on the page %s', $this->getSession()
        ->getCurrentUrl()));
    }
    foreach ($rows as $row) {
      if (strpos($row->getText(), $search) !== FALSE) {
        return $row;
      }
    }
    throw new \Exception(sprintf('Failed to find a row containing "%s" on the page %s', $search, $this->getSession()
      ->getCurrentUrl()));
  }

  /**
   * Pass if the page title is the given string.
   *
   * @param string $expected_title
   *   The string the page title should be.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   *   Thrown when element doesn't exist, or the title is a different one.
   */
  public function h1titleEquals(string $expected_title): void {
    $title_element = $this->getSession()->getPage()->find('css', 'h1');
    if (!$title_element) {
      throw new ExpectationException('No h1 element found on the page', $this->getSession()
        ->getDriver());
    }
    $actual_title = $title_element->getText();
    $this->assertTrue($expected_title === $actual_title, 'Title expected to be ' . $expected_title . ' but was ' . $actual_title);
  }

  /**
   * Pass if the specified field contains the given value.
   *
   * @param string $field
   *   The name of the field to check.
   * @param string $value
   *   The value to check.
   */
  public function assertFieldContains(string $field, string $value): void {
    $foundField = $this->getSession()->getPage()->find('named', [
      'field',
      $field,
    ]);
    if ($foundField === NULL) {
      throw new \Exception(sprintf('No field named "%s" was found on the page.', $field));
    }
    $this->assertEquals($foundField->getValue(), $value);
  }

  /**
   * Pass if the specified field does not contain the given value.
   *
   * @param string $field
   *   The name of the field to check.
   * @param string $value
   *   The value to check.
   */
  public function assertFieldNotContains(string $field, string $value): void {
    $foundField = $this->getSession()->getPage()->find('css', $field);
    if ($foundField === NULL) {
      throw new \Exception(sprintf('No field matching "%s" was found on the page.', $field));
    }
    $this->assertNotEquals($foundField->getValue(), $value);
  }

  /**
   * Pass if the specified field does not contain the given value.
   *
   * @param string $field
   *   The name of the field to check.
   * @param string $value
   *   The value to check.
   */
  public function assertEnabled(string $field, string $value): void {
    $foundField = $this->getSession()->getPage()->find('css', $field);
    if ($foundField === NULL) {
      throw new \Exception(sprintf('No field matching "%s" was found on the page.', $field));
    }
    $this->assertNotEquals($foundField->getValue(), $value);
  }

  /**
   * Click element that isn't necessarily a link.
   *
   * @param string $element
   *   The element selector.
   *
   * @throws \Exception
   */
  public function clickOn(string $element): void {
    $page = $this->getSession()->getPage();
    $findName = $page->find("css", $element);
    if (!$findName) {
      throw new \Exception($element . " could not be found");
    }
    else {
      $findName->click();
    }
  }

  /**
   * Fills in a field by selector.
   *
   * @param string $element
   *   The element selector.
   * @param string $value
   *   The value to fill in.
   *
   * @throws \Exception
   */
  public function fillInBySelector(string $element, string $value): void {
    $page = $this->getSession()->getPage();
    $findName = $page->find("css", $element);
    if (!$findName) {
      throw new \Exception($element . " could not be found");
    }
    else {
      $findName->setValue($value);
    }
  }

  /**
   * Returns all instances of the specified link.
   *
   * An optional link index may be passed.
   *
   * @param string $label
   *   Text between the anchor tags.
   *
   * @return \Behat\Mink\Element\NodeElement[]
   *   The link elements.
   */
  public function findLinkInstances(string $label): array {
    return $this->getSession()->getPage()->findAll('named_exact', [
      'link',
      $label,
    ]);
  }

  /**
   * Checks that current page contains text exactly N times.
   *
   * @param string $text
   *   The string to look for.
   * @param int $expectedCount
   *   The number of times the string should appear.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   *
   * @see \Behat\Mink\WebAssert::pageTextContains()
   */
  public function pageTextContainsNtimes(string $text, int $expectedCount): void {
    $actual = $this->getSession()->getPage()->getText();
    $normalized = preg_replace('/\s+/u', ' ', $actual);
    if ($normalized === NULL) {
      throw new \RuntimeException('Failed to normalize page text for matching.');
    }
    $regex = '/' . preg_quote($text, '/') . '/ui';
    $count = preg_match_all($regex, $normalized);
    if ($count === $expectedCount) {
      return;
    }

    if ($count !== $expectedCount) {
      $message = sprintf('The text "%s" appears in the text of this page %s times, but it should appear %s times: %s', $text, $count, $expectedCount, $normalized);
    }
    else {
      $message = sprintf('The text "%s" was not found anywhere in the text of the current page: %s', $text, $normalized);
    }

    throw new ResponseTextException($message, $this->getSession()->getDriver());
  }

  /**
   * Checks that specific element is visible.
   *
   * @param string $selectorType
   *   Element selector type (css, xpath).
   * @param string|array<int, string> $selector
   *   Element selector.
   * @param \Behat\Mink\Element\ElementInterface|null $container
   *   Document to check against.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function elementVisible(string $selectorType, string|array $selector, ?ElementInterface $container = NULL): void {
    $element = $this->assertSession()->elementExists($selectorType, $selector, $container);
    $this->assertTrue($element->isVisible());
  }

  /**
   * Checks that specific element is not visible.
   *
   * @param string $selectorType
   *   Element selector type (css, xpath).
   * @param string|array<int, string> $selector
   *   Element selector.
   * @param \Behat\Mink\Element\ElementInterface|null $container
   *   Document to check against.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function elementNotVisible(string $selectorType, string|array $selector, ?ElementInterface $container = NULL): void {
    $element = $this->assertSession()->elementExists($selectorType, $selector, $container);
    $this->assertFalse($element->isVisible());
  }

  /**
   * Assert element's content is too long and line-clamped.
   *
   * @param string $selector
   *   CSS style element selector.
   *
   * @throws \Exception
   *
   * @Then the :selector element should be line-clamped
   */
  public function theElementShouldBeLineClamped(string $selector): void {
    $offset_height = $this->getSession()->evaluateScript("return jQuery('$selector').prop('offsetHeight')");
    $scroll_height = $this->getSession()->evaluateScript("return jQuery('$selector').prop('scrollHeight')");

    /* If line-clamp is active, the element's content remains the same in the
     * page source, but visually the text is truncated and appended at the line-
     * clamp line count limit with ….
     * Since the content is still whole and the … isn't actually inserted into
     * the content, the line-clamp can be detected when the offset height is
     * less than the scroll height. Otherwise, fail the test and throw an
     * exception.
     */
    if (!($offset_height < $scroll_height)) {
      throw new \Exception(sprintf('Expected %s to be line-clamped', $selector));
    }
  }

  /**
   * Assert element's content is not too long and not line-clamped.
   *
   * @param string $selector
   *   CSS style element selector.
   *
   * @throws \Exception
   *
   * @Then the :selector element should not be line-clamped
   */
  public function theElementShouldNotBeLineClamped(string $selector): void {
    $offset_height = $this->getSession()->evaluateScript("return jQuery('$selector').prop('offsetHeight')");
    $scroll_height = $this->getSession()->evaluateScript("return jQuery('$selector').prop('scrollHeight')");

    /* If line-clamp isn't active, then  the offset height and scroll height
     * should be the same. Otherwise, fail the test and throw an exception.
     */
    if ($offset_height != $scroll_height) {
      throw new \Exception(sprintf('Expected %s to not be line-clamped', $selector));
    }
  }

  /**
   * Asserts that a select option in the current page is checked.
   *
   * @param string $select
   *   The select element.
   * @param string $optionValue
   *   The value of the option to check.
   *
   * @throws \Exception
   */
  public function assertOptionSelected(string $select, string $optionValue): void {
    $selectElement = $this->getSession()
      ->getPage()
      ->find('named', ['select', "{$select}"]);
    if ($selectElement === NULL) {
      throw new \Exception(sprintf('The select box with "%s" not found on current page.', $select));
    }
    $optionElement = $selectElement->find('named', [
      'option',
      "{$optionValue}",
    ]);
    if ($optionElement === NULL) {
      throw new \Exception(sprintf('The select box "%s" has no option "%s".', $select, $optionValue));
    }
    if (!$optionElement->hasAttribute("selected")) {
      throw new \Exception(sprintf('The select box with "%s" has nothing selected', $select));
    }
    if (!$optionElement->getAttribute("selected") == "selected") {
      throw new \Exception(sprintf('The select box "%s" should have had %s selected.', $select, $optionValue));
    }
  }

  /**
   * Asserts that the given select element has the given options.
   *
   * @param array<int, string> $expectedOptions
   *   The expected options.
   * @param string $id
   *   The id of the select element.
   * @param string $message
   *   The message.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  protected function assertSelectOptionsExact(array $expectedOptions, string $id, string $message = ''): void {
    foreach ($expectedOptions as $expectedOption) {
      $this->assertSession()->optionExists($id, $expectedOption);
    }
    $visibilitySelect = $this->assertSession()->selectExists($id);
    $this->assertCount(count($expectedOptions), $visibilitySelect->findAll('css', 'option'), $message);
  }

  /**
   * Returns the updated ID of a select, used after AJAX updates it.
   *
   * @param string $idBeginsWith
   *   The base ID of the select.
   *
   * @return string|null
   *   The new element's ID, or NULL if there is not one.
   */
  protected function getUpdatedIdAfterAjax(string $idBeginsWith): ?string {
    $selects = $this->getCurrentPage()->findAll('css', 'select');
    /** @var \Behat\Mink\Element\NodeElement $select */
    foreach ($selects as $select) {
      $selectId = $select->getAttribute('id');
      if ($selectId !== NULL && strpos($selectId, $idBeginsWith) === 0) {
        return $selectId;
      }
    }
    return NULL;
  }

  /**
   * Get the instance variable to use in Javascript.
   *
   * @param string $instanceId
   *   The instanceId used by the WYSIWYG module to identify the instance.
   *
   * @return string
   *   A Javascript expression representing the WYSIWYG instance.
   *
   * @throws \Exception
   */
  protected function getWysiwygInstance(string $instanceId): string {
    $instance = $this->getSession()->evaluateScript("
      return (function() {
        if (typeof Drupal !== 'undefined' && Drupal.CKEditor5Instances) {
          for (const [key, editor] of Drupal.CKEditor5Instances.entries()) {
            if (editor.sourceElement && editor.sourceElement.getAttribute('data-drupal-selector') === '$instanceId') {
              return 'ck5';
            }
            if (editor.sourceElement && editor.sourceElement.id === '$instanceId') {
              return 'ck5';
            }
          }
        }
        if (typeof CKEDITOR !== 'undefined' && CKEDITOR.instances['$instanceId']) {
          return 'ck4';
        }
        return false;
      })();
    ");

    if (!$instance) {
      throw new \Exception(sprintf('The editor "%s" was not found on the page %s', $instanceId, $this->getSession()->getCurrentUrl()));
    }

    if ($instance === 'ck5') {
      return "(function() {
        for (const [key, editor] of Drupal.CKEditor5Instances.entries()) {
          if ((editor.sourceElement && editor.sourceElement.getAttribute('data-drupal-selector') === '$instanceId') ||
              (editor.sourceElement && editor.sourceElement.id === '$instanceId')) {
            return editor;
          }
        }
      })()";
    }

    return "CKEDITOR.instances['$instanceId']";
  }

  /**
   * Types text into wysiwyg field.
   *
   * @param string $instanceId
   *   The instanceId used by the WYSIWYG module to identify the instance.
   * @param string $text
   *   The text to type.
   *
   * @throws \Exception
   */
  public function typeInWysiwygEditor(string $instanceId, string $text): void {
    $instance = $this->getWysiwygInstance($instanceId);
    $this->getSession()->executeScript("
      var editor = $instance;
      if (editor.model) {
        editor.model.change(writer => {
          editor.model.insertContent(writer.createText(\"$text\"));
        });
      } else {
        editor.insertText(\"$text\");
      }
    ");
  }

  /**
   * Fills in wysiwyg field.
   *
   * @param string $instanceId
   *   The instanceId used by the WYSIWYG module to identify the instance.
   * @param string $text
   *   The text to type.
   *
   * @throws \Exception
   */
  public function fillInWysiwygEditor(string $instanceId, string $text): void {
    $instance = $this->getWysiwygInstance($instanceId);
    $this->getSession()->executeScript("($instance).setData(\"$text\");");
  }

  /**
   * Asserts that wysiwyg field contains the given text.
   *
   * @param string $instanceId
   *   The instanceId used by the WYSIWYG module to identify the instance.
   * @param string $text
   *   The text to type.
   *
   * @throws \Exception
   */
  public function assertContentInWysiwygEditor(string $instanceId, string $text): void {
    $instance = $this->getWysiwygInstance($instanceId);
    $content = $this->getSession()->evaluateScript("return ($instance).getData()");
    if (!is_string($content)) {
      throw new \Exception(sprintf('Could not read content from the "%s" WYSIWYG editor on the page %s', $instanceId, $this->getSession()->getCurrentUrl()));
    }
    if (!str_contains($content, $text)) {
      throw new \Exception(sprintf('The text "%s" was not found in the "%s" WYSWIYG editor on the page %s: %s', $text, $instanceId, $this->getSession()->getCurrentUrl(), $content));
    }
  }

  /**
   * Fills in wysiwyg field.
   *
   * @param string $locator
   *   The locator of the field.
   * @param string $value
   *   The value to type.
   *
   * @throws \Exception
   */
  public function iFillInWysiwygOnFieldWith(string $locator, string $value): void {
    $el = $this->getSession()->getPage()->findField($locator);
    if ($el === NULL) {
      throw new \Exception('Could not find a field with locator: ' . $locator);
    }
    $fieldId = $el->getAttribute('id');

    if (empty($fieldId)) {
      throw new \Exception('Could not find an id for field with locator: ' . $locator);
    }

    $instance = $this->getWysiwygInstance($fieldId);
    $this->getSession()->executeScript("$instance.setData(\"$value\");");
  }

  /**
   * Assert active link in left nav.
   *
   * @param string $link
   *   Text of link.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function assertActivePathInLeftNav(string $link): void {
    $left_nav = $this->assertSession()->elementExists('css', '#navbar-collapse');

    if (!$left_nav->findLink($link)) {
      throw new \Exception(sprintf('Expecting to find a %s link in the left nav, but did not.', $link));
    }

    if ($active_link = $left_nav->find('css', 'ul.menu li.menu-item--active-trail')) {
      $text = $active_link->getText();
      if ($text != $link) {
        throw new \Exception(sprintf('Expecting active link to be %s in left nav, but active link is %s.', $link, $text));
      }
    }
    else {
      throw new \Exception(sprintf('Expecting active link to be %s in left nav, but there are no active links.', $link));
    }
  }

  /**
   * Finds a link by href.
   *
   * @param string $href
   *   The href to find.
   *
   * @return \Behat\Mink\Element\NodeElement[]
   *   The links found.
   */
  public function findLinkByHref(string $href) {
    $xpath = $this->assertSession()->buildXPathQuery('//a[contains(@href, :href)]', [':href' => $href]);
    return $this->getCurrentPage()->findAll('xpath', $xpath);
  }

  /**
   * Asserts that a tab does not exist.
   *
   * @param string $tabName
   *   The name of the tab.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function assertTabNotExists(string $tabName): void {
    $localTasks = $this->assertSession()->elementExists('css', 'nav.tabs-primary');
    $this->assertFalse($localTasks->hasLink($tabName));
  }

  /**
   * Asserts that the page title is the given string.
   *
   * @param string $title
   *   The title to check for.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ElementTextException
   */
  public function assertPageTitle(string $title): void {
    $this->assertSession()->elementTextContains('css', 'div.block-page-title-block h1', $title);
  }

  /**
   * Follows the meta refresh tag. Useful for testing a batch job.
   */
  protected function iFollowMetaRefresh(): void {
    $consecutiveMisses = 0;
    while ($consecutiveMisses < 3) {
      $refresh = $this->getSession()
        ->getPage()
        ->find('css', 'meta[http-equiv="Refresh"]');
      if ($refresh) {
        $content = $refresh->getAttribute('content');
        if ($content === NULL) {
          $consecutiveMisses++;
          continue;
        }
        $consecutiveMisses = 0;
        $url = str_replace('0; URL=', '', $content);
        $this->getSession()->visit($url);
      }
      else {
        $consecutiveMisses++;
        if ($consecutiveMisses < 3) {
          sleep(1);
        }
      }
    }
  }

}

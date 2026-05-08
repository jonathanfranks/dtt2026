<?php

namespace Drupal\Tests\jackotopia\ExistingSite;

use weitzman\DrupalTestTraits\EntityCrawlerTrait;

/**
 * Demos DTT's EntityCrawlerTrait — render entities/blocks without an HTTP hit.
 *
 * No drupalGet, no theme, no regions, no blocks pulled in. Just the entity
 * render array, rendered in isolation, returned as a Symfony DomCrawler so we
 * can poke at it with CSS selectors.
 */
class EntityCrawlerStuffTest extends JackotopiaTestBase {

  use EntityCrawlerTrait;

  /**
   * The teaser view mode trims the body; the default view mode doesn't.
   */
  public function testArticleTeaserTrimsTheBody(): void {
    // Article's teaser view mode uses 'text_summary_or_trimmed' at 600 chars.
    // We want a body where a unique token sits past that boundary, so the
    // trim is observable: full view contains it, teaser view does not.
    $token = 'JACKOTOPIA_TAIL';
    $body = str_repeat('Lorem ipsum dolor sit amet. ', 30) . $token;
    $this->assertGreaterThan(600, strpos($body, $token), 'Our token needs to sit past the 600-char trim point.');

    $node = $this->createNode([
      'type' => 'article',
      'title' => 'Crawler subject',
      'body' => ['value' => $body, 'format' => 'basic_html'],
      'status' => 1,
    ]);

    $full = $this->getRenderedEntityCrawler($node, 'default');
    $teaser = $this->getRenderedEntityCrawler($node, 'teaser');

    // Title shows in both view modes.
    $this->assertSame('Crawler subject', trim($full->filter('h2 a, h1 a, h2, h1')->first()->text()));
    $this->assertStringContainsString('Crawler subject', $teaser->text());

    // Body token: present in default, absent from teaser.
    $this->assertStringContainsString($token, $full->text());
    $this->assertStringNotContainsString($token, $teaser->text());
  }

  /**
   * The 'powered_by' block plugin renders a Drupal-promoting blurb.
   */
  public function testPoweredByBlockPlugin(): void {
    $crawler = $this->getBlockPluginCrawler('system_powered_by_block');

    $this->assertStringContainsString('Powered by', $crawler->text());
    $href = $crawler->filter('a')->first()->attr('href');
    $this->assertNotNull($href);
    $this->assertStringContainsString('drupal.org', $href);
  }

}

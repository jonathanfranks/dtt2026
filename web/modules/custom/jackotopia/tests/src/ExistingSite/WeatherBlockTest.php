<?php

namespace Drupal\Tests\jackotopia\ExistingSite;

use weitzman\DrupalTestTraits\EntityCrawlerTrait;

/**
 * Tests the jackotopia_weather block two ways: in isolation, and on the page.
 *
 * The block plugin crawler test is fast — it instantiates the block with a
 * specific zip and renders only that block. Good for asserting "given this
 * config, the block produces this markup" without booting a page.
 *
 * The on-page test is slower but proves the actual placement: two block
 * config entities live in the sidebar region (60614 + 44718), and both show
 * up wired to the right zip when a real page is served.
 */
class WeatherBlockTest extends JackotopiaTestBase {

  use EntityCrawlerTrait;

  /**
   * Block plugin crawler — render the block in isolation, with config.
   */
  public function testBlockRendersForConfiguredZip() {
    $crawler = $this->getBlockPluginCrawler('jackotopia_weather', ['zip_code' => '60614']);

    $this->assertSame('60614', $crawler->filter('.jackotopia-weather')->attr('data-zip'));
    $this->assertMatchesRegularExpression(
      '/^60614: -?\d+(\.\d+)? °F$/',
      trim($crawler->filter('.jackotopia-weather__temp')->text()),
    );
  }

  /**
   * Block plugin crawler against a different zip — proves config drives output.
   */
  public function testBlockRendersForDifferentZip() {
    $crawler = $this->getBlockPluginCrawler('jackotopia_weather', ['zip_code' => '44718']);

    $this->assertSame('44718', $crawler->filter('.jackotopia-weather')->attr('data-zip'));
    $this->assertStringStartsWith('44718:', trim($crawler->filter('.jackotopia-weather__temp')->text()));
  }

  /**
   * Both placed blocks render on a real page in the sidebar.
   */
  public function testBothPlacedBlocksAppearInSidebar() {
    // Front page renders the Olivero front-end theme with the sidebar region.
    $this->drupalGet('/');

    $page = $this->getSession()->getPage();

    // Each placement has a stable HTML id derived from its block config id.
    $chicago = $page->find('css', '#block-olivero-weather-60614');
    $canton = $page->find('css', '#block-olivero-weather-44718');
    $this->assertNotNull($chicago, 'The 60614 block placement is on the page.');
    $this->assertNotNull($canton, 'The 44718 block placement is on the page.');

    // Each placement renders the temp wired to its own zip.
    $this->assertMatchesRegularExpression(
      '/^60614: -?\d+(\.\d+)? °F$/',
      trim($chicago->find('css', '.jackotopia-weather__temp')->getText()),
    );
    $this->assertMatchesRegularExpression(
      '/^44718: -?\d+(\.\d+)? °F$/',
      trim($canton->find('css', '.jackotopia-weather__temp')->getText()),
    );
  }

}

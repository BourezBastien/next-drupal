<?php

namespace Drupal\Tests\next\Kernel;

use Drupal\Core\Link;
use Drupal\KernelTests\KernelTestBase;
use Drupal\next\Entity\NextSite;

/**
 * Tests the next_site list builder.
 *
 * @group next
 *
 * @coversDefaultClass \Drupal\next\NextSiteListBuilder
 */
class NextSiteListBuilderTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['next', 'system', 'user'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');
    $this->installConfig(['next']);
  }

  /**
   * @covers ::buildRow
   */
  public function testBaseUrlIsRenderedAsLink() {
    $list_builder = $this->container->get('entity_type.manager')
      ->getListBuilder('next_site');

    $blog = NextSite::create([
      'id' => 'blog',
      'label' => 'Blog',
      'base_url' => 'https://blog.com',
    ]);
    $blog->save();

    $row = $list_builder->buildRow($blog);
    $this->assertInstanceOf(Link::class, $row['base_url']);
    $this->assertSame('https://blog.com', $row['base_url']->getUrl()
      ->toString());
    $this->assertSame('_blank', $row['base_url']->getUrl()
      ->getOption('attributes')['target']);
  }

  /**
   * @covers ::buildRow
   */
  public function testInvalidBaseUrlIsRenderedAsPlainText() {
    $list_builder = $this->container->get('entity_type.manager')
      ->getListBuilder('next_site');

    $misconfigured = NextSite::create([
      'id' => 'misconfigured',
      'label' => 'Misconfigured',
      'base_url' => 'not a url',
    ]);
    $misconfigured->save();

    $row = $list_builder->buildRow($misconfigured);
    $this->assertSame('not a url', (string) $row['base_url']);
  }

}

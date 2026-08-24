<?php

namespace Drupal\Tests\next\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\next\Entity\NextEntityTypeConfig;
use Drupal\next\Entity\NextSite;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\node\Traits\NodeCreationTrait;

/**
 * Tests the site preview entity operation.
 *
 * @group next
 */
class NextEntityOperationTest extends KernelTestBase {

  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['filter', 'next', 'node', 'system', 'user'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installConfig(['filter', 'next', 'system', 'user']);
    $this->installSchema('node', ['node_access']);

    NodeType::create(['type' => 'page', 'name' => 'Page'])->save();
    NodeType::create(['type' => 'article', 'name' => 'Article'])->save();

    NextSite::create([
      'label' => 'Blog',
      'id' => 'blog',
      'base_url' => 'https://blog.com',
    ])->save();

    NextEntityTypeConfig::create([
      'id' => 'node.page',
      'site_resolver' => 'site_selector',
      'configuration' => [
        'sites' => [
          'blog' => 'blog',
        ],
      ],
    ])->save();
  }

  /**
   * Tests that configured entity types get a site preview operation.
   */
  public function testSitePreviewOperation() {
    $page = $this->createNode(['type' => 'page']);
    $operations = next_entity_operation($page);

    $this->assertArrayHasKey('site_preview', $operations);
    $this->assertSame('Site preview', (string) $operations['site_preview']['title']);
    $this->assertSame($page->toUrl('canonical')->toString(), $operations['site_preview']['url']->toString());

    // Entity types without a next entity type config get no operation.
    $article = $this->createNode(['type' => 'article']);
    $operations = next_entity_operation($article);
    $this->assertArrayNotHasKey('site_preview', $operations);
  }

}

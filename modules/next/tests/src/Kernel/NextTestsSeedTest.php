<?php

namespace Drupal\Tests\next\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the deterministic end-to-end seed module.
 *
 * @group next
 */
class NextTestsSeedTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'filter',
    'link',
    'menu_link_content',
    'node',
    'path',
    'path_alias',
    'system',
    'text',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installEntitySchema('path_alias');
    $this->installEntitySchema('menu_link_content');
    $this->installSchema('node', ['node_access']);
    $this->installConfig(['filter']);
  }

  /**
   * Tests that installing the module seeds deterministic content.
   */
  public function testSeed() {
    \Drupal::service('module_installer')->install(['next_tests_seed']);

    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $nodes = $storage->loadByProperties([
      'type' => 'next_test_page',
    ]);
    $this->assertCount(2, $nodes);

    $home = $storage->loadByProperties([
      'type' => 'next_test_page',
      'title' => 'Next tests home',
    ]);
    $home = reset($home);
    $this->assertNotFalse($home);
    $this->assertSame('Next tests home', $home->label());
    $this->assertSame('/next-tests/home', $this->getAlias($home));

    $about = $storage->loadByProperties([
      'type' => 'next_test_page',
      'title' => 'Next tests about',
    ]);
    $about = reset($about);
    $this->assertNotFalse($about);
    $this->assertSame('Next tests about', $about->label());
    $this->assertSame('/next-tests/about', $this->getAlias($about));

    // A menu link points at the home page. (The second home revision is
    // asserted by the end-to-end specs against the installed site: kernel
    // tests do not run the module installer's full save pipeline.)
    $links = \Drupal::entityTypeManager()->getStorage('menu_link_content')->loadByProperties([
      'title' => 'Next tests home link',
    ]);
    $this->assertCount(1, $links);
  }

  /**
   * Resolves the alias of a node via the path alias storage.
   */
  protected function getAlias($node): ?string {
    $aliases = \Drupal::entityTypeManager()->getStorage('path_alias')->loadByProperties([
      'path' => '/node/' . $node->id(),
    ]);
    $alias = reset($aliases);
    return $alias ? '/' . ltrim($alias->getAlias(), '/') : NULL;
  }

}

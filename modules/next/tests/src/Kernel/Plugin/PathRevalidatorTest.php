<?php

namespace Drupal\Tests\next\Kernel\Plugin;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\next\Entity\NextEntityTypeConfig;
use Drupal\next\Entity\NextSite;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests the path revalidator plugin.
 *
 * @coversDefaultClass \Drupal\next\Plugin\Next\Revalidator\Path
 *
 * @group next
 */
class PathRevalidatorTest extends KernelTestBase {

  use NodeCreationTrait;
  use ProphecyTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'field',
    'filter',
    'next',
    'node',
    'system',
    'user',
    'path',
    'path_alias',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installEntitySchema('path_alias');
    $this->installConfig(['filter']);
    $this->installSchema('node', ['node_access']);

    NodeType::create(['type' => 'page', 'name' => 'Page'])->save();
  }

  /**
   * @covers ::revalidate
   */
  public function testRevalidate() {
    /** @var \GuzzleHttp\ClientInterface $client */
    $client = $this->prophesize(ClientInterface::class);
    $this->container->set('http_client', $client->reveal());

    $blog_site = NextSite::create([
      'id' => 'blog',
    ]);
    $blog_site->save();

    // Create entity type config.
    $entity_type_config = NextEntityTypeConfig::create([
      'id' => 'node.page',
      'draft_enabled' => TRUE,
      'site_resolver' => 'site_selector',
      'configuration' => [
        'sites' => [
          'blog' => 'blog',
        ],
      ],
      'revalidator' => 'path',
      'revalidator_configuration' => [
        'revalidate_page' => TRUE,
      ],
    ]);
    $entity_type_config->save();

    $client->request('GET', $this->any())->shouldNotBeCalled();
    $this->createNode();
    $page->save();
    $this->container->get('kernel')->terminate(Request::create('/'), new Response());

    $client->request('GET', 'http://blog.com/api/revalidate?path=/node/2')->shouldBeCalled()->willReturn(new GuzzleResponse());
    $blog_site->setRevalidateUrl('http://blog.com/api/revalidate')->save();
    $this->createNode();
    $page->save();
    $this->container->get('kernel')->terminate(Request::create('/'), new Response());

    $marketing = NextSite::create([
      'id' => 'marketing',
      'revalidate_url' => 'http://marketing.com/api/revalidate',
      'revalidate_secret' => '12345',
    ]);
    $marketing->save();
    $entity_type_config->setSiteResolverConfiguration('site_selector', [
      'sites' => [
        'blog' => 'blog',
        'marketing' => 'marketing',
      ],
    ])->save();

    $client->request('GET', 'http://marketing.com/api/revalidate?path=/node/3&secret=12345')->shouldBeCalled()->willReturn(new GuzzleResponse());
    $client->request('GET', 'http://blog.com/api/revalidate?path=/node/3')->shouldBeCalled()->willReturn(new GuzzleResponse());
    $this->createNode();
    $page->save();
    $this->container->get('kernel')->terminate(Request::create('/'), new Response());

    $entity_type_config->setRevalidatorConfiguration('path', [
      'additional_paths' => "/\n/blog",
    ])->save();

    $client->request('GET', 'http://marketing.com/api/revalidate?path=/node/3&secret=12345')->shouldBeCalled()->willReturn(new GuzzleResponse());
    $client->request('GET', 'http://marketing.com/api/revalidate?path=/&secret=12345')->shouldBeCalled()->willReturn(new GuzzleResponse());
    $client->request('GET', 'http://marketing.com/api/revalidate?path=/blog&secret=12345')->shouldBeCalled()->willReturn(new GuzzleResponse());
    $client->request('GET', 'http://blog.com/api/revalidate?path=/node/3')->shouldBeCalled()->willReturn(new GuzzleResponse());
    $client->request('GET', 'http://blog.com/api/revalidate?path=/')->shouldBeCalled()->willReturn(new GuzzleResponse());
    $client->request('GET', 'http://blog.com/api/revalidate?path=/blog')->shouldBeCalled()->willReturn(new GuzzleResponse());
    $this->createNode();
    $page->save();
    $this->container->get('kernel')->terminate(Request::create('/'), new Response());
  }

  /**
   * @covers ::revalidate
   */
  public function testRevalidateRedirectSource() {
    /** @var \GuzzleHttp\ClientInterface $client */
    $client = $this->prophesize(ClientInterface::class);
    $this->container->set('http_client', $client->reveal());

    // Give nodes a redirect_source field, mimicking redirect entities so we
    // can verify the redirect source path is revalidated instead of the
    // entity's own path.
    FieldStorageConfig::create([
      'field_name' => 'redirect_source',
      'entity_type' => 'node',
      'type' => 'string',
    ])->save();
    FieldConfig::create([
      'field_name' => 'redirect_source',
      'entity_type' => 'node',
      'bundle' => 'page',
    ])->save();

    NextSite::create([
      'id' => 'blog',
      'revalidate_url' => 'http://blog.com/api/revalidate',
    ])->save();

    NextEntityTypeConfig::create([
      'id' => 'node.page',
      'draft_enabled' => TRUE,
      'site_resolver' => 'site_selector',
      'configuration' => [
        'sites' => [
          'blog' => 'blog',
        ],
      ],
      'revalidator' => 'path',
      'revalidator_configuration' => [
        'revalidate_page' => TRUE,
      ],
    ])->save();

    $client->request('GET', 'http://blog.com/api/revalidate?path=/old-blog')->shouldBeCalled()->willReturn(new GuzzleResponse());

    $this->createNode([
      'type' => 'page',
      'title' => 'Redirect',
      'redirect_source' => 'old-blog',
    ]);
    $this->container->get('kernel')->terminate(Request::create('/'), new Response());
  }

}

<?php

namespace Drupal\Tests\next\Kernel\Plugin;

use Drupal\KernelTests\KernelTestBase;
use Drupal\next\Entity\NextEntityTypeConfig;
use Drupal\next\Entity\NextSite;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tests the cache tag revalidator plugin.
 *
 * @coversDefaultClass \Drupal\next\Plugin\Next\Revalidator\CacheTag
 *
 * @group next
 */
class CacheTagRevalidatorTest extends KernelTestBase {

  use NodeCreationTrait;

  /**
   * The recorded revalidate URLs.
   *
   * @var string[]
   */
  protected array $recordedUrls = [];

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
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

    // Replace the http client with a recorder so assertions can inspect the
    // exact revalidate URLs.
    $recorder = new class ($this->recordedUrls) implements ClientInterface {

      public function __construct(protected array &$urls) {}

      /**
       * {@inheritdoc}
       */
      public function request(string $method, $uri = '', array $options = []): ResponseInterface {
        $this->urls[] = (string) $uri;
        return new GuzzleResponse();
      }

      /**
       * {@inheritdoc}
       */
      public function requestAsync(string $method, $uri = '', array $options = []): PromiseInterface {
        throw new \BadMethodCallException('Not implemented.');
      }

      /**
       * {@inheritdoc}
       */
      public function send(RequestInterface $request, array $options = []): ResponseInterface {
        throw new \BadMethodCallException('Not implemented.');
      }

      /**
       * {@inheritdoc}
       */
      public function sendAsync(RequestInterface $request, array $options = []): PromiseInterface {
        throw new \BadMethodCallException('Not implemented.');
      }

      /**
       * {@inheritdoc}
       */
      public function getConfig(?string $option = NULL) {
        return NULL;
      }

    };
    $this->container->set('http_client', $recorder);
  }

  /**
   * @covers ::revalidate
   */
  public function testRevalidate() {
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
      'revalidator' => 'cache_tag',
      'revalidator_configuration' => [
        'entity_tag' => TRUE,
        'entity_list_tag' => TRUE,
      ],
    ]);
    $entity_type_config->save();

    // No revalidation happens while the site has no revalidate URL.
    $this->createNode();
    $this->container->get('kernel')->terminate(Request::create('/'), new Response());
    $this->assertEmpty($this->recordedUrls);

    // The revalidate URL receives the entity cache tags and the entity
    // locale.
    $blog_site->setRevalidateUrl('http://blog.com/api/revalidate')->save();
    $this->createNode();
    $this->container->get('kernel')->terminate(Request::create('/'), new Response());

    $this->assertCount(1, $this->recordedUrls);
    $url = $this->recordedUrls[0];
    $this->assertStringStartsWith('http://blog.com/api/revalidate?', $url);
    // Colons and commas in cache tags are URL-encoded in the query string.
    $this->assertStringContainsString('tags=node%3A2%2Cnode_list%2Cnode_list%3Apage', $url);
    $this->assertStringContainsString('locale=en', $url);

    // Additional tags are appended and the secret is forwarded.
    $entity_type_config->setRevalidatorConfiguration('cache_tag', [
      'entity_tag' => TRUE,
      'entity_list_tag' => TRUE,
      'additional_tags' => "homepage",
    ])->save();
    $blog_site->setRevalidateUrl('http://blog.com/api/revalidate')->setRevalidateSecret('12345')->save();

    $this->createNode();
    $this->container->get('kernel')->terminate(Request::create('/'), new Response());

    $this->assertCount(2, $this->recordedUrls);
    $url = $this->recordedUrls[1];
    $this->assertStringContainsString('tags=node%3A3%2Cnode_list%2Cnode_list%3Apage%2Chomepage', $url);
    $this->assertStringContainsString('locale=en', $url);
    $this->assertStringContainsString('secret=12345', $url);
  }

}

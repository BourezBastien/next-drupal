<?php

namespace Drupal\Tests\next\Kernel\Plugin;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Url;
use Drupal\next\Entity\NextEntityTypeConfig;
use Drupal\next\Entity\NextSite;
use Drupal\next\Event\EntityActionEvent;
use Drupal\next\Event\EntityActionEventInterface;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Prophecy\Argument;
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
    'content_translation',
    'field',
    'filter',
    'language',
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
    $page = $this->createNode();
    $page->save();
    $this->container->get('kernel')->terminate(Request::create('/'), new Response());

    $client->request('GET', 'http://blog.com/api/revalidate?path=/node/2')->shouldBeCalled()->willReturn(new GuzzleResponse());
    $blog_site->setRevalidateUrl('http://blog.com/api/revalidate')->save();
    $page = $this->createNode();
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
    $page = $this->createNode();
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
    $page = $this->createNode();
    $page->save();
    $this->container->get('kernel')->terminate(Request::create('/'), new Response());
  }

  /**
   * @covers ::revalidate
   */
  public function testRevalidateSuspendsSiteAfterFailure() {
    /** @var \GuzzleHttp\ClientInterface $client */
    $client = $this->prophesize(ClientInterface::class);
    $this->container->set('http_client', $client->reveal());

    NextSite::create([
      'id' => 'blog',
      'revalidate_url' => 'http://blog.com/api/revalidate',
    ])->save();

    NextEntityTypeConfig::create([
      'id' => 'node.page',
      'site_resolver' => 'site_selector',
      'configuration' => [
        'sites' => [
          'blog' => 'blog',
        ],
      ],
      'revalidator' => 'path',
      'revalidator_configuration' => [
        'revalidate_page' => TRUE,
        'additional_paths' => '/listing',
      ],
    ])->save();

    // The first request to the site fails: further requests to the same
    // site must be suspended instead of stacking timeouts. (#695)
    $client->request('GET', 'http://blog.com/api/revalidate?path=/node/1')
      ->shouldBeCalledTimes(1)
      ->willThrow(new \Exception('Connection timed out.'));
    $client->request('GET', 'http://blog.com/api/revalidate?path=/listing')
      ->shouldNotBeCalled();

    $this->createNode(['type' => 'page']);
    $this->container->get('kernel')->terminate(Request::create('/'), new Response());
  }

  /**
   * @covers ::revalidate
   */
  public function testRevalidateTokensInAdditionalPaths() {
    /** @var \GuzzleHttp\ClientInterface $client */
    $client = $this->prophesize(ClientInterface::class);
    $this->container->set('http_client', $client->reveal());

    NextSite::create([
      'id' => 'blog',
      'revalidate_url' => 'http://blog.com/api/revalidate',
    ])->save();

    NextEntityTypeConfig::create([
      'id' => 'node.page',
      'site_resolver' => 'site_selector',
      'configuration' => [
        'sites' => [
          'blog' => 'blog',
        ],
      ],
      'revalidator' => 'path',
      'revalidator_configuration' => [
        'revalidate_page' => FALSE,
        'additional_paths' => "/node/[node:nid]\n/static-path",
      ],
    ])->save();

    $client->request('GET', 'http://blog.com/api/revalidate?path=/node/1')
      ->shouldBeCalled()
      ->willReturn(new GuzzleResponse());
    $client->request('GET', 'http://blog.com/api/revalidate?path=/static-path')
      ->shouldBeCalled()
      ->willReturn(new GuzzleResponse());

    $this->createNode(['type' => 'page']);
    $this->container->get('kernel')->terminate(Request::create('/'), new Response());
  }

  /**
   * @covers ::revalidate
   */
  public function testRevalidateFailureIsLogged() {
    /** @var \GuzzleHttp\ClientInterface $client */
    $client = $this->prophesize(ClientInterface::class);
    $this->container->set('http_client', $client->reveal());

    /** @var \Drupal\Core\Logger\LoggerChannelInterface|\Prophecy\Prophecy\ObjectProphecy $logger */
    $logger = $this->prophesize(LoggerChannelInterface::class);
    $this->container->set('logger.channel.next', $logger->reveal());

    NextSite::create([
      'id' => 'blog',
      'revalidate_url' => 'http://blog.com/api/revalidate',
    ])->save();

    NextEntityTypeConfig::create([
      'id' => 'node.page',
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

    $client->request('GET', 'http://blog.com/api/revalidate?path=/node/1')
      ->shouldBeCalled()
      ->willReturn(new GuzzleResponse(500));

    // Non-200 responses must surface in the logs, not pass silently.
    $logger->warning(
      Argument::containingString('Failed to revalidate path'),
      Argument::any()
    )->shouldBeCalled();

    $this->createNode(['type' => 'page']);
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

  /**
   * @covers ::revalidate
   */
  public function testRevalidateDeletedTranslation() {
    // Enable Dutch and content translation for page nodes.
    ConfigurableLanguage::createFromLangcode('nl')->save();
    $this->container->get('content_translation.manager')
      ->setEnabled('node', 'page', TRUE);

    $page = $this->createNode([
      'type' => 'page',
      'title' => 'Test page',
    ]);
    $page->addTranslation('nl', ['title' => 'Testpagina'])->save();

    // The entity action event created for the deleted translation must
    // carry the translation's language, so that URL generation resolves to
    // the localized path of the deleted translation.
    $event = EntityActionEvent::createFromEntity(
      $page->getTranslation('nl'),
      EntityActionEventInterface::DELETE_ACTION
    );

    $url = $event->getEntityUrl();
    $this->assertInstanceOf(Url::class, $url);
    $this->assertSame(
      'nl',
      $url->getOption('language')->getId(),
      'The entity URL carries the translation language.'
    );

    // Sanity check: the event for the default translation keeps the
    // default language.
    $event = EntityActionEvent::createFromEntity(
      $page,
      EntityActionEventInterface::DELETE_ACTION
    );
    $this->assertSame('en', $event->getEntityUrl()->getOption('language')->getId());
  }

}

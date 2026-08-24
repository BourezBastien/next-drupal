<?php

namespace Drupal\next\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Url;

/**
 * Defines an entity action event.
 *
 * @see \Drupal\next\Event\EntityEvents
 */
class EntityActionEvent extends Event implements EntityActionEventInterface {

  /**
   * The entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected EntityInterface $entity;

  /**
   * The sites for this entity.
   *
   * @var \Drupal\next\Entity\NextSiteInterface[]
   */
  protected array $sites;

  /**
   * The action.
   *
   * @var string
   */
  protected string $action;

  /**
   * The entity Url.
   *
   * @var \Drupal\Core\Url|null
   */
  protected ?Url $entityUrl;

  /**
   * EntityActionEvent constructor.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param string $action
   *   The action.
   * @param array $sites
   *   The sites for the entity.
   * @param \Drupal\Core\Url|null $entity_url
   *   The entity url.
   */
  public function __construct(EntityInterface $entity, string $action, array $sites, ?Url $entity_url) {
    $this->entity = $entity;
    $this->action = $action;
    $this->sites = $sites;
    $this->entityUrl = $entity_url;
  }

  /**
   * Helper to create an entity action event.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param string $action
   *   The action.
   *
   * @return \Drupal\next\Event\EntityActionEvent
   *   An instance of entity action event.
   */
  public static function createFromEntity(EntityInterface $entity, string $action): self {
    /** @var \Drupal\next\NextEntityTypeManagerInterface $next_entity_type_manager */
    $next_entity_type_manager = \Drupal::service('next.entity_type.manager');

    $sites = $next_entity_type_manager->getSitesForEntity($entity);
    // Pass the entity language so translated entities resolve to the URL of
    // the translation that changed, e.g. when a translation is deleted.
    $url = $entity->hasLinkTemplate('canonical')
      ? $entity->toUrl('canonical', ['language' => $entity->language()])
      : NULL;

    // Redirect entities (redirect module) have an admin canonical URL which
    // is never cached by the frontend. Revalidate the redirect source path
    // instead, so the page serving the old redirect is purged.
    if ($entity instanceof FieldableEntityInterface && $entity->hasField('redirect_source')) {
      $item = $entity->get('redirect_source')->first();
      // The redirect module stores the source in the path property; fall
      // back to value for simple string fields.
      $value = $item ? $item->getValue() : [];
      $source_path = $value['path'] ?? $value['value'] ?? NULL;
      if ($source_path) {
        try {
          $url = Url::fromUserInput('/' . ltrim($source_path, '/'));
        }
        catch (\InvalidArgumentException $exception) {
          // Keep the canonical URL when the source path is invalid.
        }
      }
    }

    return new static($entity, $action, $sites, $url);
  }

  /**
   * {@inheritdoc}
   */
  public function getEntity(): EntityInterface {
    return $this->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setEntity(EntityInterface $entity): EntityActionEventInterface {
    $this->entity = $entity;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSites(): array {
    return $this->sites;
  }

  /**
   * {@inheritdoc}
   */
  public function setSites(array $sites): EntityActionEventInterface {
    $this->sites = $sites;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAction(): string {
    return $this->action;
  }

  /**
   * {@inheritdoc}
   */
  public function setAction(string $action): EntityActionEventInterface {
    $this->action = $action;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityUrl(): ?Url {
    return $this->entityUrl;
  }

  /**
   * {@inheritdoc}
   */
  public function setEntityUrl(Url $url): EntityActionEventInterface {
    $this->entityUrl = $url;
    return $this;
  }

}

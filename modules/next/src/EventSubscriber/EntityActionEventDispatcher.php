<?php

namespace Drupal\next\EventSubscriber;

use Drupal\Core\DestructableInterface;
use Drupal\next\Event\EntityActionEvent;
use Drupal\next\Event\EntityEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Defines an event subscriber for dispatching entity events.
 */
final class EntityActionEventDispatcher implements DestructableInterface {

  /**
   * The events to dispatch.
   *
   * @var \Drupal\next\Event\EntityActionEvent[]
   */
  private array $events = [];

  /**
   * EntityActionEventDispatcher constructor.
   */
  public function __construct(
    private EventDispatcherInterface $eventDispatcher,
  ) {
  }

  /**
   * Adds an event to be dispatched at the end of the request.
   *
   * @param \Drupal\next\Event\EntityActionEvent $event
   *   The event.
   */
  public function addEvent(EntityActionEvent $event): void {
    $this->events[] = $event;
  }

  /**
   * {@inheritdoc}
   */
  public function destruct() {
    $events = $this->events;
    // Reset the queue before dispatching so events are not dispatched again
    // if destruct() runs more than once in the same process (e.g. in tests,
    // Drush commands or queue workers).
    $this->events = [];
    foreach ($events as $event) {
      $this->eventDispatcher->dispatch($event, EntityEvents::ENTITY_ACTION);
    }
  }

}

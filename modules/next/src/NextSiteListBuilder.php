<?php

namespace Drupal\next;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of next_site entities.
 *
 * @see \Drupal\next\Entity\NextSite
 */
class NextSiteListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['uuid'] = $this->t('UUID');
    $header['id'] = $this->t('ID');
    $header['label'] = $this->t('Label');
    $header['base_url'] = $this->t('Base URL');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\next\Entity\NextSiteInterface $entity */
    $row['uuid'] = $entity->uuid();
    $row['id'] = $entity->id();
    $row['label'] = $entity->label();

    $base_url = $entity->getBaseUrl();
    if ($base_url && filter_var($base_url, FILTER_VALIDATE_URL)) {
      $row['base_url'] = Link::fromTextAndUrl($base_url, Url::fromUri($base_url, [
        'attributes' => ['target' => '_blank'],
      ]));
    }
    else {
      // Keep the raw value when it is not a valid URL so site builders can
      // still spot and fix misconfigured sites.
      $row['base_url'] = $base_url ?: $this->t('N/A');
    }

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    $operations['environment_variables'] = [
      'title' => $this->t('Environment variables'),
      'url' => $entity->toUrl('environment-variables'),
    ];

    return $operations;
  }

}

<?php

namespace Drupal\next\Form;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Base form for next_site.
 */
class NextSiteForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\next\Entity\NextSiteInterface $entity */
    $entity = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#description' => $this->t('Example: Blog or Marketing site.'),
      '#maxlength' => 255,
      '#default_value' => $entity->label(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $entity->id(),
      '#machine_name' => [
        'exists' => '\Drupal\next\Entity\NextSite::load',
      ],
      '#disabled' => !$entity->isNew(),
    ];

    $form['base_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Base URL'),
      '#description' => $this->t('Enter the base URL for the Next.js site. Example: <em>https://example.com</em>.'),
      '#default_value' => $entity->getBaseUrl(),
      '#required' => TRUE,
    ];

    $form['settings'] = [
      '#type' => 'vertical_tabs',
      '#title' => $this->t('Settings'),
    ];

    $form['preview'] = [
      '#title' => $this->t('Draft Mode'),
      '#description' => $this->t('Draft mode (or the deprecated Preview mode) allows editors to preview content on the site. You can read more on the <a href=":uri" target="_blank">Next.js documentation</a>.', [
        ':uri' => 'https://nextjs.org/docs/app/building-your-application/configuring/draft-mode',
      ]),
      '#type' => 'details',
      '#group' => 'settings',
    ];

    $form['preview']['preview_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Draft URL (or Preview URL)'),
      '#description' => $this->t('Enter the draft URL or preview URL. Example: <em>https://example.com/api/draft</em> or <em>https://example.com/api/preview</em>.'),
      '#default_value' => $entity->getPreviewUrl(),
    ];

    $form['preview']['preview_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Secret key'),
      '#description' => $this->t('Enter a secret for the site draft/preview. This must be unique for each Next.js site'),
      '#default_value' => $entity->getPreviewSecret(),
    ];

    $form['preview']['generate_preview_secret'] = $this->buildGenerateSecretButton('preview_secret');

    $form['revalidation'] = [
      '#title' => $this->t('On-demand Revalidation'),
      '#description' => $this->t('On-demand revalidation updates your pages when content is updated on your Drupal site. You can read more on the <a href=":uri" target="_blank">Next.js documentation</a>.', [
        ':uri' => 'https://nextjs.org/docs/app/building-your-application/data-fetching/fetching-caching-and-revalidating#revalidating-data',
      ]),
      '#type' => 'details',
      '#group' => 'settings',
    ];

    $form['revalidation']['revalidate_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Revalidate URL'),
      '#description' => $this->t('Enter the revalidate URL. Example: <em>https://example.com/api/revalidate</em>.'),
      '#default_value' => $entity->getRevalidateUrl(),
    ];

    $form['revalidation']['revalidate_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Revalidate secret'),
      '#description' => $this->t('Enter a secret for the site revalidate. This is the same value used for <em>DRUPAL_REVALIDATE_SECRET</em>.'),
      '#default_value' => $entity->getRevalidateSecret(),
    ];

    $form['revalidation']['generate_revalidate_secret'] = $this->buildGenerateSecretButton('revalidate_secret');

    return $form;
  }

  /**
   * Builds a button that fills the given secret field with a secure value.
   *
   * @param string $field
   *   The name of the secret field the button generates a value for.
   *
   * @return array
   *   The button render array.
   */
  protected function buildGenerateSecretButton(string $field): array {
    return [
      '#type' => 'submit',
      '#value' => $this->t('Generate secret'),
      '#submit' => ['::generateSecretSubmit'],
      // Only the target field matters: skip validation so an incomplete
      // form does not block secret generation.
      '#limit_validation_errors' => [],
      '#generate_secret_for' => $field,
    ];
  }

  /**
   * Submit callback: fills the target secret field with a secure value.
   */
  public function generateSecretSubmit(array &$form, FormStateInterface $form_state): void {
    $element = $form_state->getTriggeringElement();
    $field = $element['#generate_secret_for'] ?? NULL;
    if (!$field) {
      return;
    }

    $secret = Crypt::randomBytesBase64(32);
    $form_state->setValue($field, $secret);

    // Text fields rebuild from raw user input: overwrite it too so the
    // generated secret is displayed.
    $user_input = $form_state->getUserInput();
    $user_input[$field] = $secret;
    $form_state->setUserInput($user_input);

    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\next\Entity\NextSiteInterface $next_site */
    $next_site = $this->entity;
    $status = $next_site->save();

    $this->messenger()->addStatus($this->t('Next.js site %label has been %action.', [
      '%label' => $next_site->label(),
      '%action' => $status === SAVED_NEW ? 'added' : 'updated',
    ]));

    $form_state->setRedirectUrl($next_site->toUrl('collection'));

    return $status;
  }

}

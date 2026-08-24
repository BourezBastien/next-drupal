<?php

namespace Drupal\Tests\next\Kernel;

use Drupal\Core\Form\FormState;
use Drupal\KernelTests\KernelTestBase;
use Drupal\next\Entity\NextSite;
use Drupal\next\Form\NextSiteForm;

/**
 * Tests the next_site entity form.
 *
 * @group next
 *
 * @coversDefaultClass \Drupal\next\Form\NextSiteForm
 */
class NextSiteFormTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['next', 'system', 'user'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(['next', 'system']);
  }

  /**
   * @covers ::form
   */
  public function testSecretGenerationButtons() {
    $site = NextSite::create([
      'label' => 'Blog',
      'id' => 'blog',
    ]);

    $form = $this->container->get('entity.form_builder')->getForm($site, 'add');

    $this->assertArrayHasKey('generate_preview_secret', $form['preview']);
    $this->assertSame('preview_secret', $form['preview']['generate_preview_secret']['#generate_secret_for']);
    $this->assertArrayHasKey('generate_revalidate_secret', $form['revalidation']);
    $this->assertSame('revalidate_secret', $form['revalidation']['generate_revalidate_secret']['#generate_secret_for']);
  }

  /**
   * @covers ::generateSecretSubmit
   */
  public function testGenerateSecretSubmit() {
    $form_state = new FormState();
    $form_state->setTriggeringElement([
      '#generate_secret_for' => 'preview_secret',
    ]);
    $form_state->setUserInput(['preview_secret' => 'insecure']);

    $form = [];
    $entity_form = NextSiteForm::create($this->container);
    $entity_form->generateSecretSubmit($form, $form_state);

    $secret = $form_state->getValue('preview_secret');
    $this->assertNotSame('insecure', $secret);
    $this->assertGreaterThan(40, strlen((string) $secret));
    $this->assertSame($secret, $form_state->getUserInput()['preview_secret']);
    $this->assertTrue($form_state->isRebuilding());
  }

  /**
   * @covers ::generateSecretSubmit
   */
  public function testGenerateSecretSubmitWithoutTarget() {
    $form_state = new FormState();
    $form_state->setTriggeringElement([]);
    $form_state->setUserInput(['preview_secret' => 'unchanged']);

    $form = [];
    $entity_form = NextSiteForm::create($this->container);
    $entity_form->generateSecretSubmit($form, $form_state);

    $this->assertSame('unchanged', $form_state->getUserInput()['preview_secret']);
    $this->assertFalse($form_state->isRebuilding());
  }

}

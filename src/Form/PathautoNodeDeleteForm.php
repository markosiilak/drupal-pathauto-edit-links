<?php

namespace Drupal\pathauto_edit_links\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Form\NodeDeleteForm;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Form controller for node delete forms accessed via pathauto URLs.
 */
class PathautoNodeDeleteForm extends NodeDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pathauto_node_delete_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $path = NULL) {
    // Get the node from the pathauto path
    $node = $this->getNodeFromPath($path);
    
    if (!$node) {
      throw new NotFoundHttpException();
    }

    // Set the entity for the form
    $this->setEntity($node);
    
    // Build the normal node delete form
    $form = parent::buildForm($form, $form_state);
    
    // Modify the form action to point back to the pathauto URL
    $form['#action'] = '/' . $path . '/delete';
    
    // Store the pathauto path for use in submit handlers
    $form_state->set('pathauto_path', $path);
    
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Call the parent submit
    parent::submitForm($form, $form_state);
    
    // After deletion, redirect to the front page or a custom location
    $form_state->setRedirectUrl(Url::fromRoute('<front>'));
  }

  /**
   * Get node from pathauto path.
   *
   * @param string $path
   *   The pathauto path.
   *
   * @return \Drupal\node\NodeInterface|null
   *   The node or null if not found.
   */
  protected function getNodeFromPath($path) {
    if (!$path) {
      return NULL;
    }
    
    $alias_manager = \Drupal::service('path_alias.manager');
    $alias = '/' . $path;
    $system_path = $alias_manager->getPathByAlias($alias);
    
    if (preg_match('/^\/node\/(\d+)$/', $system_path, $matches)) {
      $node_id = $matches[1];
      return \Drupal::entityTypeManager()->getStorage('node')->load($node_id);
    }
    
    return NULL;
  }

}

<?php

namespace Drupal\pathauto_edit_links\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeForm;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Form controller for node edit forms accessed via pathauto URLs.
 */
class PathautoNodeEditForm extends NodeForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'pathauto_node_edit_form';
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
    
    // Build the normal node edit form
    $form = parent::buildForm($form, $form_state);
    
    // Modify the form action to point back to the pathauto URL
    $form['#action'] = '/' . $path . '/edit';
    
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
    
    // Get the pathauto path
    $pathauto_path = $form_state->get('pathauto_path');
    
    if ($pathauto_path) {
      // Redirect back to the pathauto view URL after save
      $form_state->setRedirectUrl(Url::fromUserInput('/' . $pathauto_path));
    }
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

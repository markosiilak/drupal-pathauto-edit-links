<?php

namespace Drupal\pathauto_edit_links\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Controller for handling pathauto edit URLs.
 */
class PathautoEditController extends ControllerBase {

  /**
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity form builder.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface
   */
  protected $entityFormBuilder;

  /**
   * Constructs a PathautoEditController object.
   *
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The path alias manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityFormBuilderInterface $entity_form_builder
   *   The entity form builder.
   */
  public function __construct(AliasManagerInterface $alias_manager, EntityTypeManagerInterface $entity_type_manager, EntityFormBuilderInterface $entity_form_builder) {
    $this->aliasManager = $alias_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFormBuilder = $entity_form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('path_alias.manager'),
      $container->get('entity_type.manager'),
      $container->get('entity.form_builder')
    );
  }

  /**
   * Handles edit requests for pathauto aliases.
   *
   * @param string $path
   *   The path parameter from the URL.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return array
   *   A render array for the node edit form.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   If the path doesn't correspond to a valid node.
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   If the user doesn't have access to edit the node.
   */
  public function editPage($path, Request $request) {
    $node = $this->getNodeFromPath($path);
    
    // Check access
    if (!$node->access('update')) {
      throw new AccessDeniedHttpException();
    }
    
    // Return the node edit form
    return $this->entityFormBuilder->getForm($node, 'default');
  }

  /**
   * Handles delete requests for pathauto aliases.
   *
   * @param string $path
   *   The path parameter from the URL.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return array
   *   A render array for the node delete form.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   If the path doesn't correspond to a valid node.
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   If the user doesn't have access to delete the node.
   */
  public function deletePage($path, Request $request) {
    $node = $this->getNodeFromPath($path);
    
    // Check access
    if (!$node->access('delete')) {
      throw new AccessDeniedHttpException();
    }
    
    // Return the node delete form
    return $this->entityFormBuilder->getForm($node, 'delete');
  }

  /**
   * Handles revisions requests for pathauto aliases.
   *
   * @param string $path
   *   The path parameter from the URL.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect to the node revisions page.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   If the path doesn't correspond to a valid node.
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   If the user doesn't have access to view revisions.
   */
  public function revisionsPage($path, Request $request) {
    $node = $this->getNodeFromPath($path);
    
    // Check access (using view access as a basic check)
    if (!$node->access('view')) {
      throw new AccessDeniedHttpException();
    }
    
    // Redirect to the actual revisions page since it's complex
    $revisions_url = Url::fromRoute('entity.node.version_history', ['node' => $node->id()]);
    return new TrustedRedirectResponse($revisions_url->toString());
  }

  /**
   * Custom access callback for pathauto edit routes.
   *
   * @param string $path
   *   The path parameter from the URL.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function checkEditAccess($path, AccountInterface $account) {
    try {
      $node = $this->getNodeFromPath($path);
      
      // Check if the user has access to edit this node
      if ($node->access('update', $account)) {
        return AccessResult::allowed();
      }
      
      return AccessResult::forbidden();
    } catch (NotFoundHttpException $e) {
      return AccessResult::forbidden();
    }
  }

  /**
   * Custom access callback for pathauto delete routes.
   *
   * @param string $path
   *   The path parameter from the URL.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function checkDeleteAccess($path, AccountInterface $account) {
    try {
      $node = $this->getNodeFromPath($path);
      
      // Check if the user has access to delete this node
      if ($node->access('delete', $account)) {
        return AccessResult::allowed();
      }
      
      return AccessResult::forbidden();
    } catch (NotFoundHttpException $e) {
      return AccessResult::forbidden();
    }
  }

  /**
   * Title callback for pathauto edit routes.
   *
   * @param string $path
   *   The path parameter from the URL.
   *
   * @return string
   *   The page title.
   */
  public function getTitle($path) {
    try {
      $node = $this->getNodeFromPath($path);
      return $this->t('Edit @title', ['@title' => $node->getTitle()]);
    } catch (NotFoundHttpException $e) {
      return $this->t('Edit');
    }
  }

  /**
   * Helper method to get a node from a pathauto alias path.
   *
   * @param string $path
   *   The path parameter from the URL.
   *
   * @return \Drupal\node\NodeInterface
   *   The loaded node.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   If the path doesn't correspond to a valid node.
   */
  private function getNodeFromPath($path) {
    // Handle multi-segment paths properly
    // The path parameter might contain slashes for nested paths like "podcast/test-episode"
    $alias = '/' . $path;
    
    // Get the system path from the alias
    $system_path = $this->aliasManager->getPathByAlias($alias);
    
    // Check if this is a node path
    if (preg_match('/^\/node\/(\d+)$/', $system_path, $matches)) {
      $node_id = $matches[1];
      
      // Load the node to verify it exists
      $node = $this->entityTypeManager->getStorage('node')->load($node_id);
      
      if ($node) {
        return $node;
      }
    }
    
    // If we get here, the path doesn't correspond to a valid node
    throw new NotFoundHttpException('The requested path "' . $alias . '" does not correspond to a valid node.');
  }

}
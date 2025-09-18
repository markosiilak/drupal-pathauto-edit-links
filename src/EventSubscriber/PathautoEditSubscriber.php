<?php

namespace Drupal\pathauto_edit_links\EventSubscriber;

use Drupal\path_alias\AliasManagerInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event subscriber for pathauto edit URLs.
 */
class PathautoEditSubscriber implements EventSubscriberInterface {

  /**
   * The path alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Constructs a PathautoEditSubscriber object.
   *
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The path alias manager.
   */
  public function __construct(AliasManagerInterface $alias_manager) {
    $this->aliasManager = $alias_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onRequest', 100];
    return $events;
  }

  /**
   * Handles the request event.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The request event.
   */
  public function onRequest(RequestEvent $event) {
    $request = $event->getRequest();
    $path = $request->getPathInfo();

    // Only process paths that end with /edit, /delete, or /revisions
    // Skip if this is already a /node/ID/action path to prevent redirect loops
    if (preg_match('/^\/node\/\d+\/(edit|delete|revisions)$/', $path)) {
      return;
    }

    // Check if this is a path ending with /edit, /delete, or /revisions
    if (preg_match('/^(.+)\/(edit|delete|revisions)$/', $path, $matches)) {
      $alias = $matches[1];
      $action = $matches[2];
      
      // Skip admin paths and other system paths
      if (preg_match('/^\/?(admin|user|batch|system)/', $alias)) {
        return;
      }
      
      // Get the system path from the alias
      $system_path = $this->aliasManager->getPathByAlias($alias);
      
      // Only proceed if the alias actually resolves to a different system path
      if ($alias === $system_path) {
        return;
      }
      
      // Check if this is a node path
      if (preg_match('/^\/node\/(\d+)$/', $system_path, $node_matches)) {
        $node_id = $node_matches[1];
        
        // Load the node to check if it exists
        $node_storage = \Drupal::entityTypeManager()->getStorage('node');
        $node = $node_storage->load($node_id);
        
        if ($node) {
          // Redirect to the appropriate node action
          $route_name = '';
          switch ($action) {
            case 'edit':
              $route_name = 'entity.node.edit_form';
              break;
            case 'delete':
              $route_name = 'entity.node.delete_form';
              break;
            case 'revisions':
              $route_name = 'entity.node.version_history';
              break;
          }
          
          if ($route_name) {
            $url = Url::fromRoute($route_name, ['node' => $node_id]);
            $response = new TrustedRedirectResponse($url->toString());
            $event->setResponse($response);
          }
        }
      }
    }
  }

}

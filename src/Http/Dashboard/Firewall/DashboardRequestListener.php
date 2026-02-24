<?php

namespace App\Http\Dashboard\Firewall;

use App\Http\Dashboard\Controller\BaseController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Protects dashboard controllers by enforcing CMS_MANAGE
 * on any controller extending BaseController.
 */
readonly class DashboardRequestListener implements EventSubscriberInterface
{
    private const string REQUIRED_ROLE = 'CMS_MANAGE';

    public function __construct(
        private AuthorizationCheckerInterface $authChecker,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ControllerEvent::class => ['onController', 100],
        ];
    }

    /**
     * Checks access when a controller is resolved.
     *
     * If the controller is an instance of BaseController,
     * the user must have the `CMS_MANAGE` permission.
     *
     * This acts as a defensive layer to prevent access even if
     * the route prefix changes or is misconfigured.
     *
     * @throws AccessDeniedException
     */
    public function onController(ControllerEvent $event): void
    {
        if (false === $event->isMainRequest()) {
            return;
        }

        $controllerObject = $this->extractControllerObject($event->getController());

        if ($controllerObject instanceof BaseController && !$this->authChecker->isGranted(self::REQUIRED_ROLE)) {
            throw $this->rejectRequest($event->getRequest());
        }
    }

    private function extractControllerObject(mixed $controller): ?object
    {
        if (is_array($controller) && isset($controller[0]) && is_object($controller[0])) {
            return $controller[0];
        }

        return is_object($controller) ? $controller : null;
    }

    /**
     * Creates an AccessDeniedException with the current request as subject.
     */
    private function rejectRequest(Request $request): AccessDeniedException
    {
        $exception = new AccessDeniedException();
        $exception->setSubject($request);

        return $exception;
    }
}

<?php

namespace App\EventListener;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProfileCompletionListener
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        // Ignore debug/profiler/internal routes
        if (!$route || str_starts_with($route, '_')) {
            return;
        }

        // Allowed routes even if profile is incomplete
        $allowedRoutes = [
            'app_login',
            'app_logout',
            'app_register',
            'app_profile',
            'app_profile_edit',
        ];

        if (in_array($route, $allowedRoutes, true)) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();

        if (!$user instanceof User) {
            return;
        }

        $profile = $user->getUserProfile();

        $isComplete =
            $profile
            && trim((string) $profile->getFullName()) !== ''
            && trim((string) $profile->getCity()) !== ''
            && trim((string) $profile->getPhone()) !== '';

        if ($isComplete) {
            return;
        }

        $event->setResponse(new RedirectResponse(
            $this->urlGenerator->generate('app_profile_edit')
        ));
    }
}

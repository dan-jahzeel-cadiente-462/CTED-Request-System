<?php

namespace App\Twig;

use App\Repository\RequestRepository;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class AdminRequestBadgeExtension extends AbstractExtension implements GlobalsInterface
{
    private const SESSION_KEY = 'admin.requests.last_seen_at';

    public function __construct(private RequestStack $requestStack, private RequestRepository $requestRepository)
    {
    }

    public function getGlobals(): array
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if ($currentRequest === null) {
            return ['adminRequestBadgeCount' => 0];
        }

        $session = $currentRequest->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }

        $route = $currentRequest->attributes->get('_route');
        $lastSeen = $session->get(self::SESSION_KEY);

        if ($route === 'app_admin_requests') {
            $session->set(self::SESSION_KEY, new DateTimeImmutable());
            return ['adminRequestBadgeCount' => 0];
        }

        if (!$lastSeen instanceof DateTimeImmutable) {
            $lastSeen = new DateTimeImmutable();
            $session->set(self::SESSION_KEY, $lastSeen);
            return ['adminRequestBadgeCount' => 0];
        }

        $count = $this->requestRepository->countRequestsSince($lastSeen);

        return ['adminRequestBadgeCount' => $count];
    }
}

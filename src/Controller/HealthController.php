<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Routing\Attribute\Route;

final class HealthController extends AbstractController
{
    #[Route('/health', name: 'app_health')]
    public function index(): JsonResponse
    {

        return new JsonResponse([
            'status' => 'ok',
            'time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
        ]);
    }
}

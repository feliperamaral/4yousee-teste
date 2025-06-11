<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    public function __construct(
        string $environment,
        bool $debug,
    ) {

        if ($_SERVER && isset($_SERVER['REQUEST_URI']) && str_starts_with($_SERVER['REQUEST_URI'], '/health')) {
            $debug = false;
        }

        parent::__construct($environment, $debug);
    }
    use MicroKernelTrait;
}

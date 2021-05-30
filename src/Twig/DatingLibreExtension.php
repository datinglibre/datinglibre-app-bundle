<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Twig;

use Exception;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DatingLibreExtension extends AbstractExtension
{
    private string $siteName;
    private string $siteDescription;

    public function __construct(string $siteName, string $siteDescription)
    {
        $this->siteName = $siteName;
        $this->siteDescription = $siteDescription;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('datinglibre', [$this, 'datinglibre']),
        ];
    }

    /**
     * @throws Exception
     */
    public function datinglibre(string $configurationItem): string
    {
        switch ($configurationItem) {
            case 'site_name':
                return $this->siteName;
            case 'site_description':
                return $this->siteDescription;
            default:
                throw new Exception('Unrecognized configurationItem');
        }
    }
}

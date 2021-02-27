<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use DatingLibre\AppBundle\Repository\CityRepository;
use DatingLibre\AppBundle\Repository\CountryRepository;
use DatingLibre\AppBundle\Repository\RegionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Uid\Uuid;

class LocationController extends AbstractController
{
    private CountryRepository $countryRepository;
    private RegionRepository $regionRepository;
    private CityRepository $citiesRepository;

    public function __construct(
        CountryRepository $countryRepository,
        RegionRepository $regionRepository,
        CityRepository $citiesRepository
    ) {
        $this->countryRepository = $countryRepository;
        $this->regionRepository = $regionRepository;
        $this->citiesRepository = $citiesRepository;
    }

    public function displayRegions(Uuid $countryId): Response
    {
        if (null == $countryId) {
            throw $this->createNotFoundException('Country does not exist');
        }

        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);

        return new Response($serializer->serialize($this->regionRepository->findByCountry($countryId), 'json'));
    }

    public function displayCities(Uuid $regionId): Response
    {
        if (null == $regionId) {
            throw $this->createNotFoundException('Region does not exist');
        }

        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);

        return new Response($serializer->serialize($this->citiesRepository->findByRegion($regionId), 'json'));
    }
}

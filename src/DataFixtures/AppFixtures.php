<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\DataFixtures;

use DatingLibre\AppBundle\Entity\Attribute;
use DatingLibre\AppBundle\Entity\Category;
use DatingLibre\AppBundle\Entity\Interest;
use DatingLibre\AppBundle\Entity\User;
use DatingLibre\AppBundle\Entity\City;
use DatingLibre\AppBundle\Entity\Country;
use DatingLibre\AppBundle\Entity\Region;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;
    private ObjectManager $objectManager;
    private array $categories;
    private array $attributes;
    private array $interests;

    public function __construct(
        UserPasswordHasherInterface $hasher,
        array $categories,
        array $attributes,
        array $interests
    ) {
        $this->hasher = $hasher;
        $this->categories = $categories;
        $this->attributes = $attributes;
        $this->interests = $interests;
    }

    public function load(ObjectManager $manager): void
    {
        $this->objectManager = $manager;

        $this->createTestUser();
        $this->createLocations();

        foreach ($this->interests as $interest) {
            $this->createInterest($interest);
        }

        foreach ($this->categories as $categoryName) {
            $category = $this->createCategory($categoryName);

            foreach ($this->attributes[$categoryName] as $attributeName) {
                $this->createAttribute($category, $attributeName);
            }
        }
    }

    /**
     * Solely for testing the UI, will be removed by behat tests
     */
    private function createTestUser()
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setEnabled(true);
        $user->setPassword($this->hasher->hashPassword($user, 'password'));
        $this->objectManager->persist($user);
        $this->objectManager->flush();
    }

    private function createLocations(): void
    {
        $unitedKingdom = $this->createCountry('United Kingdom');
        $unitedStates = $this->createCountry('United States');

        $newYorkState = $this->createRegion($unitedStates, 'New York State');

        $this->createCity(
            $unitedStates,
            $newYorkState,
            'New York',
            -73.9352,
            40.7128
        );

        $england = $this->createRegion($unitedKingdom, 'England');
        $scotland = $this->createRegion($unitedKingdom, 'Scotland');

        $this->createCity(
            $unitedKingdom,
            $scotland,
            'Edinburgh',
            3.1883,
            55.9533
        );

        $this->createCity(
            $unitedKingdom,
            $england,
            'London',
            -0.125739,
            51.508530
        );

        $this->createCity(
            $unitedKingdom,
            $england,
            'Bristol',
            -2.5966,
            51.4552
        );

        $this->createCity(
            $unitedKingdom,
            $england,
            'Bath',
            -2.3617,
            51.3751
        );

        $this->createCity(
            $unitedKingdom,
            $england,
            'Oxford',
            1.2577,
            51.7520
        );
    }

    private function createAttribute(Category $category, string $name): Attribute
    {
        $attribute = new Attribute();
        $attribute->setName($name);
        $attribute->setCategory($category);
        $this->objectManager->persist($attribute);
        $this->objectManager->flush();

        return $attribute;
    }

    private function createCategory(string $name): Category
    {
        $category = new Category();
        $category->setName($name);
        $this->objectManager->persist($category);
        $this->objectManager->flush();

        return $category;
    }

    private function createCountry(string $name): Country
    {
        $country = new Country();
        $country->setName($name);
        $this->objectManager->persist($country);
        $this->objectManager->flush();

        return $country;
    }

    private function createRegion(Country $country, string $name): Region
    {
        $region = new Region();
        $region->setName($name);
        $region->setCountry($country);

        $this->objectManager->persist($region);
        $this->objectManager->flush();
        return $region;
    }

    private function createCity(Country $country, Region $region, string $name, float $longitude, float $latitude): void
    {
        $city = new City();
        $city->setName($name);
        $city->setCountry($country);
        $city->setRegion($region);
        $city->setLatitude($latitude);
        $city->setLongitude($longitude);

        $this->objectManager->persist($city);
        $this->objectManager->flush();
    }

    private function createInterest(string $interestName)
    {
        $interest = new Interest();
        $interest->setName($interestName);
        $this->objectManager->persist($interest);
        $this->objectManager->flush();
    }
}

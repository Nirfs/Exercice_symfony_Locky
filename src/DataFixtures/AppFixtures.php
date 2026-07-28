<?php

namespace App\DataFixtures;

use App\Entity\Prestation;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private Generator $faker;

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUsers($manager);
        $this->loadPrestations($manager);

        $manager->flush();
    }

    private function loadUsers(ObjectManager $manager): void
    {
        // Un compte admin avec des identifiants connus, pratique pour se connecter.
        $admin = new User();
        $admin->setEmail('admin@locky.fr');
        $admin->setNom('Admin');
        $admin->setPrenom('Super');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setDateInscription(new \DateTime('-2 years'));
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'password'));
        $manager->persist($admin);

        for ($i = 0; $i < 20; ++$i) {
            $user = new User();
            $user->setEmail($this->faker->unique()->safeEmail());
            $user->setNom($this->faker->lastName());
            $user->setPrenom($this->faker->firstName());
            $user->setDateInscription(\DateTime::createFromInterface(
                $this->faker->dateTimeBetween('-2 years', 'now')
            ));
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $manager->persist($user);
        }
    }

    private function loadPrestations(ObjectManager $manager): void
    {
        for ($i = 0; $i < 30; ++$i) {
            $prestation = new Prestation();
            $prestation->setNom($this->faker->sentence(3));
            $prestation->setExtrait($this->faker->text(200));
            $prestation->setDescription($this->faker->paragraphs(3, true));
            $prestation->setRemuneration($this->faker->randomFloat(2, 20, 1500));
            $prestation->setDateCreation(\DateTime::createFromInterface(
                $this->faker->dateTimeBetween('-2 years', 'now')
            ));
            $prestation->setTelephone($this->faker->boolean(70) ? $this->faker->phoneNumber() : null);
            $manager->persist($prestation);
        }
    }
}

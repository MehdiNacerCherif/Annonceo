<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Faker;
use App\Entity\Utilisateur;

class UtilisateurFixtures extends Fixture
{
    public function __construct (UserPasswordEncoderInterface $passwordencoder){
        $this->passwordEncoder = $passwordencoder;
    }
    
    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('fr_FR');

        for ($i=1; $i <= 5 ; $i++) {

            $email = "user" . $i . "@gmail.com";
            $pseudo = $faker->sentence(1);
    
            $prenom = $faker->sentence(1);
            $nom = $faker->sentence(1);
    
            $telephone = "0826656565";
            $date = $faker->dateTimeBetween('-1 year');

            $utilisateur = new Utilisateur();
            
            $utilisateur->setEmail($email)
            ->setPassword($this->passwordEncoder->encodePassword($utilisateur, 'user' . $i))
            ->setPseudo($pseudo)


            ->setPrenom($prenom)
            ->setNom($nom)
            ->setTelephone($telephone)
            ->setInscription($date);
            

            $manager->persist($utilisateur);  

        }

        $manager->flush();
    }
}

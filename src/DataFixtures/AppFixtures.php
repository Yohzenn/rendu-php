<?php

namespace App\DataFixtures;

use App\Entity\MuscleGroup;
use App\Entity\Exercise;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $muscleGroup1 = (new MuscleGroup())
            ->setTitle('Pectoraux');
        $manager->persist($muscleGroup1);

        $muscleGroup2 = (new MuscleGroup())
            ->setTitle('Dorsaux');
        $manager->persist($muscleGroup2);

        $muscleGroup3 = (new MuscleGroup())
            ->setTitle('Jambes');
        $manager->persist($muscleGroup3);

        $exercise1 = (new Exercise())
            ->setTitlz('Développé couché')
            ->setContent('Exercice de musculation pour les pectoraux')
            ->setMuscleGroup($muscleGroup1);
        $manager->persist($exercise1);

        $exercise2 = (new Exercise())
            ->setTitlz('Tractions')
            ->setContent('Exercice de musculation pour les dorsaux')
            ->setMuscleGroup($muscleGroup2);
        $manager->persist($exercise2);

        $exercise3 = (new Exercise())
            ->setTitlz('Squats')
            ->setContent('Exercice de musculation pour les jambes')
            ->setMuscleGroup($muscleGroup3);
        $manager->persist($exercise3);

        $manager->flush();
    }
}

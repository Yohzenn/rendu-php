<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ExerciseController extends AbstractController
{
    public function __construct(
        private readonly HttpClientInterface $httpClient
    ) {
    }

    #[Route('/exercises/{slug}', name: 'app_exercises')]
    public function index(string $slug, Request $request): Response
    {
        // Mapper les slugs aux titres des groupes musculaires
        $slugToTitle = [
            'pectoraux' => 'Pectoraux',
            'dos' => 'DOS',
            'jambes' => 'JAMBES'
        ];

        $title = $slugToTitle[strtolower($slug)] ?? ucfirst($slug);
        
        try {
            // Utiliser l'URL de la requête actuelle pour l'API (même serveur Symfony)
            $baseUrl = $request->getSchemeAndHttpHost();
            
            // Récupérer tous les groupes musculaires
            $response = $this->httpClient->request('GET', $baseUrl . '/api/muscle_groups');
            $muscleGroupsData = $response->toArray();
            
            // API Platform retourne les données dans hydra:member
            $muscleGroups = $muscleGroupsData['hydra:member'] ?? $muscleGroupsData;
            
            // Trouver le groupe musculaire par titre
            $muscleGroup = null;
            foreach ($muscleGroups as $group) {
                if (isset($group['title']) && strtoupper($group['title']) === strtoupper($title)) {
                    $muscleGroup = $group;
                    break;
                }
            }
            
            if (!$muscleGroup) {
                throw $this->createNotFoundException('Groupe musculaire non trouvé');
            }

            // Essayer d'abord de récupérer le groupe musculaire avec ses exercices via l'endpoint détaillé
            try {
                $muscleGroupDetailResponse = $this->httpClient->request('GET', $baseUrl . '/api/muscle_groups/' . $muscleGroup['id']);
                $muscleGroupDetail = $muscleGroupDetailResponse->toArray();
                
                // Si le groupe musculaire contient les exercices dans la réponse
                if (isset($muscleGroupDetail['exercises']) && is_array($muscleGroupDetail['exercises'])) {
                    $exercises = $muscleGroupDetail['exercises'];
                } else {
                    // Sinon, récupérer tous les exercices et filtrer
                    $allExercisesResponse = $this->httpClient->request('GET', $baseUrl . '/api/exercises');
                    $allExercisesData = $allExercisesResponse->toArray();
                    $allExercises = $allExercisesData['hydra:member'] ?? $allExercisesData;
                    
                    // Filtrer les exercices qui appartiennent à ce groupe musculaire
                    $exercises = array_filter($allExercises, function($exercise) use ($muscleGroup) {
                        if (!isset($exercise['muscleGroup'])) {
                            return false;
                        }
                        
                        // Si c'est une chaîne IRI
                        if (is_string($exercise['muscleGroup'])) {
                            return str_contains($exercise['muscleGroup'], '/api/muscle_groups/' . $muscleGroup['id']);
                        }
                        
                        // Si c'est un tableau avec l'ID
                        if (is_array($exercise['muscleGroup']) && isset($exercise['muscleGroup']['id'])) {
                            return $exercise['muscleGroup']['id'] == $muscleGroup['id'];
                        }
                        
                        // Si c'est un tableau avec @id
                        if (is_array($exercise['muscleGroup']) && isset($exercise['muscleGroup']['@id'])) {
                            return str_contains($exercise['muscleGroup']['@id'], '/api/muscle_groups/' . $muscleGroup['id']);
                        }
                        
                        return false;
                    });
                    
                    $exercises = array_values($exercises);
                }
            } catch (\Exception $e) {
                // Si l'endpoint détaillé échoue, récupérer tous les exercices et filtrer
                $allExercisesResponse = $this->httpClient->request('GET', $baseUrl . '/api/exercises');
                $allExercisesData = $allExercisesResponse->toArray();
                $allExercises = $allExercisesData['hydra:member'] ?? $allExercisesData;
                
                // Filtrer les exercices qui appartiennent à ce groupe musculaire
                $exercises = array_filter($allExercises, function($exercise) use ($muscleGroup) {
                    if (!isset($exercise['muscleGroup'])) {
                        return false;
                    }
                    
                    if (is_string($exercise['muscleGroup'])) {
                        return str_contains($exercise['muscleGroup'], '/api/muscle_groups/' . $muscleGroup['id']);
                    }
                    
                    if (is_array($exercise['muscleGroup']) && isset($exercise['muscleGroup']['id'])) {
                        return $exercise['muscleGroup']['id'] == $muscleGroup['id'];
                    }
                    
                    if (is_array($exercise['muscleGroup']) && isset($exercise['muscleGroup']['@id'])) {
                        return str_contains($exercise['muscleGroup']['@id'], '/api/muscle_groups/' . $muscleGroup['id']);
                    }
                    
                    return false;
                });
                
                $exercises = array_values($exercises);
            }

            return $this->render('exercise/index.html.twig', [
                'muscleGroup' => $muscleGroup,
                'exercises' => $exercises,
            ]);
        } catch (\Exception $e) {
            // Si l'API n'est pas disponible, utiliser des données de test
            $muscleGroup = [
                'id' => 1,
                'title' => $title,
            ];
            
            $exercises = [];
            
            // Données de test basées sur le groupe musculaire
            if (strtolower($slug) === 'pectoraux') {
                $exercises = [
                    [
                        'id' => 1,
                        'titlz' => 'Développé couché',
                        'content' => 'Exercice de musculation pour les pectoraux, triceps et deltoïdes',
                        'media' => null
                    ],
                    [
                        'id' => 2,
                        'titlz' => 'Pompes',
                        'content' => 'Exercice de musculation pour les pectoraux, triceps et deltoïdes',
                        'media' => null
                    ],
                ];
            } elseif (strtolower($slug) === 'dos') {
                $exercises = [
                    [
                        'id' => 3,
                        'titlz' => 'Rowing barre',
                        'content' => 'Exercice de musculation pour les dorsaux et biceps',
                        'media' => null
                    ],
                ];
            } elseif (strtolower($slug) === 'jambes') {
                $exercises = [
                    [
                        'id' => 4,
                        'titlz' => 'Fentes',
                        'content' => 'Exercice de musculation pour les quadriceps et fessiers',
                        'media' => null
                    ],
                ];
            }
            
            return $this->render('exercise/index.html.twig', [
                'muscleGroup' => $muscleGroup,
                'exercises' => $exercises,
            ]);
        }
    }
}


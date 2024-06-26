<?php
session_start();
require '../doc/autoload.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'moderator') {
    header('Location: ../doc/index.php');
    exit();
}

use MongoDB\Client;
use MongoDB\Exception\Exception;

$client = new Client("mongodb://localhost:27017");
$db = $client->mini_x;

// Fonction pour afficher les résultats dans un tableau Bootstrap
function displayTable($title, $headers, $rows)
{
    echo "<h2 class='mt-4'>$title</h2>";
    echo "<div class='table-responsive'><table class='table table-bordered table-hover'>";
    echo "<thead class='thead-dark'><tr>";
    foreach ($headers as $header) {
        echo "<th class='text-center'>$header</th>";
    }
    echo "</tr></thead><tbody>";
    foreach ($rows as $row) {
        echo "<tr>";
        foreach ($row as $cell) {
            echo "<td class='text-center'>$cell</td>";
        }
        echo "</tr>";
    }
    echo "</tbody></table></div>";
}

// Fonction pour calculer le nombre total de likes par utilisateur
function calculateTotalLikes($db)
{
    try {
        $tweetsCollection = $db->tweets;

        $pipeline = [
            [
                '$project' => [
                    'user' => 1,
                    'likes' => ['$ifNull' => ['$likes', 0]],
                    'comments' => ['$ifNull' => ['$comments', []]],
                    'totalLikes' => [
                        '$add' => [
                            ['$ifNull' => ['$likes', 0]],
                            ['$sum' => '$comments.likes']
                        ]
                    ]
                ]
            ],
            [
                '$group' => [
                    '_id' => '$user',
                    'totalLikes' => ['$sum' => '$totalLikes']
                ]
            ],
            [
                '$sort' => ['totalLikes' => -1]
            ],
            [
                '$limit' => 10
            ]
        ];

        $result = $tweetsCollection->aggregate($pipeline);
        $rows = [];
        foreach ($result as $doc) {
            $rows[] = [$doc['_id'], $doc['totalLikes']];
        }
        displayTable("Total des Likes par Utilisateur", ['Utilisateur', 'Total des Likes'], $rows);
    } catch (Exception $e) {
        echo "<div class='alert alert-danger' role='alert'>Erreur lors du calcul du nombre total de likes par utilisateur: {$e->getMessage()}</div>";
    }
}

// Fonction pour analyser l'engagement de tous les utilisateurs
function analyzeUserEngagement($db)
{
    try {
        $tweetsCollection = $db->tweets;

        $pipelineTweets = [
            [
                '$project' => [
                    'user' => 1,
                    'likes' => ['$ifNull' => ['$likes', 0]],
                    'comments' => ['$ifNull' => ['$comments', []]],
                    'totalComments' => ['$size' => ['$ifNull' => ['$comments', []]]],
                    'totalCommentsLikes' => ['$sum' => ['$ifNull' => ['$comments.likes', 0]]]
                ]
            ],
            [
                '$group' => [
                    '_id' => '$user',
                    'totalTweets' => ['$sum' => 1],
                    'totalLikes' => ['$sum' => '$likes'],
                    'totalComments' => ['$sum' => '$totalComments'],
                    'totalCommentsLikes' => ['$sum' => '$totalCommentsLikes']
                ]
            ],
            [
                '$addFields' => [
                    'totalEngagement' => [
                        '$add' => [
                            '$totalTweets',
                            '$totalComments',
                            '$totalLikes',
                            '$totalCommentsLikes'
                        ]
                    ]
                ]
            ],
            [
                '$sort' => ['totalEngagement' => -1, '_id' => 1]
            ],
            [
                '$limit' => 10
            ]
        ];

        $tweetsResult = $tweetsCollection->aggregate($pipelineTweets);

        $rows = [];
        foreach ($tweetsResult as $doc) {
            $totalLikes = $doc['totalLikes'] + $doc['totalCommentsLikes'];
            $rows[] = [$doc['_id'], $doc['totalTweets'], $doc['totalComments'], $totalLikes];
        }
        displayTable("Engagement des Utilisateurs", ['Utilisateur', 'Total des Tweets', 'Total des Commentaires', 'Total des Likes'], $rows);
    } catch (Exception $e) {
        echo "<div class='alert alert-danger' role='alert'>Erreur lors de l'analyse de l'engagement des utilisateurs: {$e->getMessage()}</div>";
    }
}

// Fonction pour identifier les top utilisateurs basés sur leurs interactions
function identifyTopUsers($db)
{
    try {
        $tweetsCollection = $db->tweets;

        $pipeline = [
            [
                '$project' => [
                    'user' => 1,
                    'comments' => ['$ifNull' => ['$comments', []]],
                    'totalInteractions' => ['$add' => [['$size' => ['$ifNull' => ['$comments', []]]], 1]]
                ]
            ],
            [
                '$group' => [
                    '_id' => '$user',
                    'totalInteractions' => ['$sum' => '$totalInteractions']
                ]
            ],
            [
                '$sort' => ['totalInteractions' => -1, '_id' => 1]
            ],
            [
                '$limit' => 10
            ]
        ];

        $result = $tweetsCollection->aggregate($pipeline);
        $rows = [];
        foreach ($result as $doc) {
            $rows[] = [$doc['_id'], $doc['totalInteractions']];
        }
        displayTable("Top Utilisateurs Basés sur les Interactions", ['Utilisateur', 'Total des Interactions'], $rows);
    } catch (Exception $e) {
        echo "<div class='alert alert-danger' role='alert'>Erreur lors de l'identification des top utilisateurs: {$e->getMessage()}</div>";
    }
}

// Fonction pour calculer les tweets les plus populaires
function calculatePopularTweets($db)
{
    try {
        $tweetsCollection = $db->tweets;

        $pipeline = [
            [
                '$project' => [
                    'user' => 1,
                    'message' => 1,
                    'likes' => ['$ifNull' => ['$likes', 0]],
                    'comments' => ['$ifNull' => ['$comments', []]],
                    'popularityScore' => [
                        '$add' => [
                            ['$ifNull' => ['$likes', 0]],
                            ['$size' => ['$ifNull' => ['$comments', []]]]
                        ]
                    ]
                ]
            ],
            [
                '$sort' => ['popularityScore' => -1, 'user' => 1]
            ],
            [
                '$limit' => 10
            ]
        ];

        $result = $tweetsCollection->aggregate($pipeline);
        $rows = [];
        foreach ($result as $doc) {
            $rows[] = [$doc['user'], $doc['message'], $doc['popularityScore']];
        }
        displayTable("Tweets les Plus Populaires", ['Utilisateur', 'Message', 'Score de Popularité'], $rows);
    } catch (Exception $e) {
        echo "<div class='alert alert-danger' role='alert'>Erreur lors du calcul des tweets les plus populaires: {$e->getMessage()}</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Analyses des Données</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        h2 {
            background-color: #343a40;
            color: #fff;
            padding: 10px;
            border-radius: 5px;
        }

        .table thead th {
            vertical-align: middle;
        }
    </style>
</head>

<body>
    <?php include '../templates/header.php'; ?>
    <div class="container mt-5">
        <h1 class="mb-4 text-center">Analyses des Données</h1>
        <?php
        calculateTotalLikes($db);
        analyzeUserEngagement($db);
        identifyTopUsers($db);
        calculatePopularTweets($db);
        ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
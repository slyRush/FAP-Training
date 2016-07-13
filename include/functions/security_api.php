<?php
// All functions to secure WS with API Key
include_once "set_headers.php";

/**
 * Génération aléatoire unique MD5 String pour utilisateur clé Api
 */
function generateApiKey()
{
    return md5(uniqid(rand(), true));
}

/**
 * Ajout de Couche intermédiaire pour authentifier chaque demande
 * Vérifier si la demande a clé API valide dans l'en-tête "Authorization"
 */
function authenticate(\Slim\Route $route) {
    $headers = apache_request_headers(); // Obtenir les en-têtes de requêtes

    // Vérification de l'en-tête d'autorisation
    if (isset($headers['Authorization'])) {
        $db = new DbUsers();

        $api_key = $headers['Authorization']; // Obtenir la clé d'api dans le header

        if (!$db->isValidApiKey($api_key)) // Valider la clé API
        {
            global $app;
            echoResponseWithParams(401, false, "Accés Refusé. Clé API invalide", NULL);
            $app->stop();
        }
        else
        {
            global $user_id;
            $user_id = $db->getUserIdByApiKey($api_key); // Obtenir l'ID utilisateur (clé primaire)
        }
    }
    else
    {
        // Clé API est absente dans la en-tête
        global $app;
        echoResponseWithParams(400, false, "Vous ne pouvez pas accéder à cette ressource. Clé API absente", NULL);
        $app->stop();
    }
}
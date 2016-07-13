<?php
/**
 * ------------------------ METHODES sur la table 'tasks' Avec AUTHENTICATION ------------------------
 */

include_once dirname(dirname(__DIR__)) . '/include/const.php';
include_once dirname(dirname(__DIR__)) . '/include/functions/set_headers.php';

require_once dirname(dirname(__DIR__)) . '/include/functions/utils.php';
require_once dirname(dirname(__DIR__)) . '/include/functions/security_api.php';
require_once dirname(dirname(__DIR__)) . '/include/db_operations/tasks.php';
require_once dirname(dirname(__DIR__)) . '/include/db_operations/user_tasks.php';

/**
 * Lister toutes les tâches d'un utilisateur particulier
 * method GET
 * url /tasks
 */
$app->get('/tasks', 'authenticate', function() {
    global $user_id;
    $response = array();
    $db = new DbTasks();

    // aller chercher toutes les tâches de l'utilisateur
    $result = $db->getAllUserTasks($user_id);

    if($result != null) {
        $response["tasks"] = array();

        // boucle au travers du résultat et de la préparation du tableau des tâches
        foreach ($result as $task) {
            $tmp = array();
            $tmp["id"] = $task["id"];
            $tmp["task"] = $task["task"];
            $tmp["status"] = $task["status"];
            $tmp["createdAt"] = $task["created_at"];
            array_push($response["tasks"], $tmp);
        }
        echoResponseWithParams(200, true, "Requete réussie, tous les tasks renvoyés", $response);
    }
    else
        echoResponseWithParams(200, false, "Pas de tâches", NULL);

});

/**
 *Lister une seule tâche d'un utilisateur particulier
 * method GET
 * url /tasks/:id
 * Retournera 404 si la tâche n'appartient pas à l'utilisateur
 */
$app->get('/tasks/:id', 'authenticate', function($task_id) {
    global $user_id;
    $response = array();
    $db = new DbTasks();

    $result = $db->getTaskByIdUser($task_id, $user_id); //chercher tâche

    if ($result != NULL) {
        $response["id"] = $result["id"];
        $response["task"] = $result["task"];
        $response["status"] = $result["status"];
        $response["createdAt"] = $result["created_at"];

        echoResponseWithParams(200, true, "Requete réussie, un task renvoyée", $response);
    }
    else
        echoResponseWithParams(404, false, "La ressource demandée n'existe pas", NULL);
});

/**
 * Création d'une nouvelle tâche dans db
 * method POST
 * params - name
 * url - /tasks/
 */
$app->post('/tasks', 'authenticate', function() use ($app) {
    verifyRequiredParams(array('task')); // vérifier les paramètres requises

    $response = array();
    $task = $app->request->post('task');

    global $user_id;
    $db = new DbTasks();

    $task_id = $db->createTask($user_id, $task); //Création d'une nouvelle tâche

    if ($task_id != NULL) {
        $response["error"] = false;
        $response["message"] = "Tâche créée avec succés";
        $response["task_id"] = $task_id;
        echoResponse(201, $response);
    } else {
        $response["error"] = true;
        $response["message"] = "Impossible de créer la tâche. S'il vous plaît essayer à nouveau";
        echoResponse(200, $response);
    }
});

/**
 * Mise à jour d'une tâche existante
 * method PUT
 * params task, status
 * url - /tasks/:id
 */
$app->put('/tasks/:id', 'authenticate', function($task_id) use($app) {
    verifyRequiredParams(array('task', 'status')); // vérifier les paramètres requises

    global $user_id;
    $app = \Slim\Slim::getInstance();

    $task = $status = "";

    if($app->request()->getContentType() == "text/plain;charset=UTF-8" || $app->request()->getContentType() == "application/json")
    {
        $request_params = json_decode($app->request()->getBody(), true);
        $task = $request_params['task'];
        $status = $request_params['status'];
    }

    $db = new DbTasks();

    // Mise à jour de la tâche
    $result = $db->updateTask($user_id, $task_id, $task, $status);
    if ($result) echoResponseWithParams(200, true, "Tâche mise à jour avec succés", NULL); // Tache mise à jour
    else echoResponseWithParams(200, false, "Le mise à jour de la tâche a échoué. La tâche est inexistante ou S'il vous plaît essayer de nouveau!", NULL); // Le mise à jour de la tâche a échoué.
});

/**
 * Suppression tâche. Les utilisateurs peuvent supprimer uniquement leurs tâches
 * method DELETE
 * url /tasks
 */
$app->delete('/tasks/:id', 'authenticate', function($task_id) use($app) {
    global $user_id;

    $db = new DbTasks();

    $result = $db->deleteTask($user_id, $task_id);
    if ($result)
        echoResponseWithParams(200, true, "Tâche supprimée avec succés", NULL); // tâche supprimée avec succés
    else
        echoResponseWithParams(200, false, "Echec de la suppression d'une tâche. S'il vous plaît essayer de nouveau!", NULL); //Echec de la suppression d'une tâche.
    //echoResponse(200, $response);
});
<?php
/**
 * Routes login manipulation - 'users' table concerned
 * ----------- METHODES sans authentification---------------------------------
 */
include_once dirname(dirname(__DIR__)) . '/include/const.php';
include_once dirname(dirname(__DIR__)) . '/include/functions/set_headers.php';

require_once dirname(dirname(__DIR__))  . '/include/functions/utils.php';
require_once dirname(dirname(__DIR__))  . '/include/db_operations/users.php';
require_once dirname(dirname(__DIR__))  . '/include/db_operations/login.php';

/**
 * Enregistrement de l'utilisateur
 * url - /register
 * methode - POST
 * params - name, email, password
 */
$app->post('/register', function() use ($app) {
    verifyRequiredParams(array('name', 'email', 'password')); // vérifier les paramédtres requises

    // lecture des params de post
    $request_params = json_decode($app->request()->getBody(), true);
    $name = $request_params['name']; //$app->request->post('name');
    $email = $request_params['email']; //$app->request->post('email');
    $password = $request_params['password']; //$app->request->post('password');

    validateEmail($email); //valider adresse email

    $db = new DbUsers();
    $res = $db->createUser($name, $email, $password);

    if ($res == USER_CREATED_SUCCESSFULLY) echoResponseWithParams(201, true, "Vous étes inscrit avec succés", NULL);
    else if ($res == USER_CREATE_FAILED) echoResponseWithParams(201, false, "Oops! Une erreur est survenue lors de l'inscription", NULL);
    else if ($res == USER_ALREADY_EXISTED) echoResponseWithParams(201, false, "Désolé, cet E-mail éxiste déja", NULL);
});

/**
 * Login Utilisateur
 * url - /login
 * method - POST
 * params - email, password
 */
$app->post('/login', function() use ($app) {
    verifyRequiredParams(array('email', 'password')); // vérifier les paramètres requises

    //recupérer les valeurs POST
    $request_params = json_decode($app->request()->getBody(), true);
    $email = $request_params["email"]; //$app->request()->post('password');
    $password = $request_params["password"]; //$app->request()->post('email');

    validateEmail($email); // valider l'adresse email

    $login = new DbLogin(); //login manipulation
    $db = new DbUsers(); // Users manipulation

    // vérifier l'Email et le mot de passe sont corrects
    if ($login->checkLogin($email, $password)) {
        $user = $db->getUserByEmail($email); // Obtenir l'utilisateur par email
        $data = array();

        if ($user != NULL) {
            if($user["status"]==1){
                //$data
                $data['name'] = $user['name'];
                $data['email'] = $user['email'];
                $data['apiKey'] = $user['api_key'];
                $data['createdAt'] = $user['created_at'];

                echoResponseWithParams(200, true, "Connexion réussie", $data);
            }
            else
                echoResponseWithParams(200, false, "Votre compte a été suspendu", NULL);
        }
        else
            echoResponseWithParams(200, false, "Une erreur est survenue. S'il vous plaît essayer à nouveau", NULL); // erreur inconnue est survenue
    }
    else
        echoResponseWithParams(200, false, "Echec de la connexion. identificateurs incorrectes", NULL); // identificateurs de l'utilisateur sont erronés
});
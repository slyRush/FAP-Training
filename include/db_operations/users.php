<?php
/**
 * Cette classe aura tous les méthodes CRUD pour la table 'users' de la base de donnée
 */

class DbUsers
{
    private $db;

    /**
     * DbUsers constructor.
     */
    public function __construct()
    {
        require_once dirname(dirname(__DIR__)) . '/libs/database_helper_crud_pdo/Db.class.php'; //use the dabatabse helper from libs
        require_once dirname(__DIR__) . '/PassHash.php'; //use hash password method
        require_once dirname(__DIR__) . '/functions/security_api.php'; //use security API to generate API key
        $this->db = new DB();
    }

    /**
     * Creation nouvel utilisateur
     * @param String $name nom complet de l'utilisateur
     * @param String $email email de connexion
     * @param String $password mot de passe de connexion
     * @return état de l'insertion
     */
    public function createUser($name, $email, $password)
    {
        // Vérifiez d'abord si l'utilisateur existe déjà dans db
        if (!$this->isUserExists($email)) {
            // requete d'insertion
            $data = array(
                "name"      => $name,
                "email"     => $email,
                "pwdhash"   => PassHash::hash($password), //Générer un hash de mot de passe
                "apikey"    => generateApiKey(), // Générer API key
                "status"    => 1
            );

            $insert_user = $this->db->query("INSERT INTO users(name, email, password_hash, api_key, status) VALUES(:name, :email, :pwdhash, :apikey, :status)", $data);

            //Vérifiez pour une insertion réussie
            if ($insert_user > 0)
            {
                return USER_CREATED_SUCCESSFULLY; // Utilisateur inséré avec succès
            }
            else
            {
                return USER_CREATE_FAILED; //Échec de la création de l'utilisateur
            }
        }
        else
        {
            return USER_ALREADY_EXISTED; //Utilisateur avec la même email existait déjà dans la db
        }


    }

    /**
     * Vérification de l'utilisateur en double par adresse e-mail
     * @param String $email email à vérifier dans la db
     * @return boolean
     */
    private function isUserExists($email)
    {
        //data binding to query request
        $data = array(
            "mail" => $email
        );

        $user = $this->db->row("SELECT id FROM users WHERE email = :mail", $data); //check si l'user existe

        return $user > 0;
    }

    /**
     *Obtention de l'utilisateur par email
     * @param String $email
     * @return user informations or NULL
     */
    public function getUserByEmail($email)
    {
        // Obtention de l'utilisateur par email
        $data = array(
            "email" => $email
        );

        $user = $this->db->row("SELECT name, email, api_key, status, created_at FROM users WHERE email = :email", $data);
        if(count($user) > 0) return $user;
        else return NULL;
    }

    /**
     * Obtention de la clé API de l'utilisateur
     * @param String $user_id clé primaire de l'utilisateur
     * @return api_key or null
     */
    public function getUserApiKeyById($user_id)
    {
        //obtenir l'API Key à partir de l'id d'un user
        $data = array(
            "id" => $user_id
        );
        $api_key = $this->db->single("SELECT api_key FROM users WHERE id = :id", $data);

        if($api_key != '' || $api_key != null) return $api_key;
        else return NULL;
    }

    /**
     * Obtention de l'identifiant de l'utilisateur par clé API
     * @param String $api_key
     * @return user_id or null
     */
    public function getUserIdByApiKey($api_key)
    {
        //obtenir l'API Key à partir de l'id d'un user
        $data = array(
            "api_key" => $api_key
        );

        $user_id = $this->db->single("SELECT id FROM users WHERE api_key = :api_key", $data);

        if($user_id != '' || $user_id != null) return $user_id;
        else return NULL;
    }

    /**
     * Validation de la clé API de l'utilisateur
     * Si la clé API est là dans db, elle est une clé valide
     * @param String $api_key
     * @return boolean
     */
    public function isValidApiKey($api_key)
    {
        //obtenir l'API Key à partir de l'id d'un user
        $data = array(
            "api_key" => $api_key
        );

        $user_id = $this->db->single("SELECT id FROM users WHERE api_key = :api_key", $data);

        if($user_id != '' || $user_id != null) return TRUE;
        else return NULL;
    }
}
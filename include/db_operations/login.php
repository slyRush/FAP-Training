<?php
/**
 * Cette classe aura les méthodes pour les login des utulisateurs
 */

class DbLogin
{
    private $db;

    /**
     * DbLogin constructor.
     */
    public function __construct()
    {
        require_once dirname(dirname(__DIR__)) . '/libs/database_helper_crud_pdo/Db.class.php'; //use the dabatabse helper from libs
        $this->db = new DB();
    }

    /**
     * Vérification de connexion de l'utilisateur
     * @param String $email
     * @param String $password
     * @return boolean Le statut de connexion utilisateur réussite / échec
     */
    public function checkLogin($email, $password)
    {
        // Obtention de l'utilisateur par email
        $data = array(
            "email" => $email
        );

        $user = $this->db->row("SELECT password_hash FROM users WHERE email = :email", $data);

        if(count($user) == 1)
        {
            if (PassHash::check_password($user['password_hash'], $password)) return TRUE; // Mot de passe utilisateur est correcte
            else return FALSE; // mot de passe utilisateur est incorrect
        }
        else return FALSE; // utilisateur n'existe pas avec l'e-mail
    }
}

//test
//$login = new DbLogin();
//$login->checkLogin('koto@mail.com', 'koto1234');
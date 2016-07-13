<?php
/**
 * Cette classe aura tous les méthodes CRUD pour la table 'user_tasks' de la base de donnée
 */

class DbUserTasks
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
     * Fonction d'assigner une tâche à l'utilisateur
     * @param String $user_id id de l'utilisateur
     * @param String $task_id id de la tâche
     */
    public function createUserTask($user_id, $task_id)
    {
        $data = array(
            "user_id" => $user_id,
            "task_id" => $task_id
        );

        $insert_user_task = $this->db->query("INSERT INTO user_tasks(user_id, task_id) values(:user_id, :task_id)", $data);

        if($insert_user_task == 1) return TRUE;
        else return FALSE;
    }

}
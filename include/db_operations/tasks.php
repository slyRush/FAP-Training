<?php
/**
 * Cette classe aura tous les méthodes CRUD pour la table 'tasks' de la base de donnée
 */

class DbTasks
{
    private $db;
    private $db_user_task;

    /**
     * DbTasks constructor.
     */
    public function __construct()
    {
        require_once dirname(dirname(__DIR__)) . '/libs/database_helper_crud_pdo/Db.class.php'; //use the dabatabse helper from libs
        require_once __DIR__ . '/user_tasks.php'; //use hash password method
        require_once dirname(__DIR__) . '/PassHash.php'; //use hash password method
        require_once dirname(__DIR__) . '/functions/security_api.php'; //use security API to generate API key
        $this->db = new DB();
        $this->db_user_task = new DbUserTasks();
    }

    /**
     * Creation nouvelle tache
     * @param String $user_id id de l'utilisateur à qui la tâche appartient
     * @param String $task texte de la tache
     * @return id task created
     */
    public function createTask($user_id, $task)
    {
        //création d'un task
        $data = array(
          "task" => $task
        );

        $insert_task = $this->db->query("INSERT INTO tasks(task) VALUES(:task)", $data);

        if($insert_task > 0) { // ligne de tâche créé, maintenant assigner la tâche à l'utilisateur
            $new_task_id = $this->db->single("SELECT id FROM tasks ORDER BY id DESC LIMIT 1"); //obtenir last id dans la table task (nouveau crée)
            $create_user_task = $this->db_user_task->createUserTask($user_id, $new_task_id);
            if($create_user_task) return $new_task_id; //task créée avec succes
            else return NULL;
        }
        else
        {
            return NULL; //task non créée
        }
    }

    /**
     * Obtention d'une seule tâche
     * @param String $task_id id de la tâche
     * @return task info
     */
    public function getTaskByIdUser($task_id, $user_id)
    {
        // Obtention de l'utilisateur par email
        $data = array(
            "id_task"       => $task_id,
            "id_user_task"  => $user_id
        );

        $tasks = $this->db->row("SELECT t.id, t.task, t.status, t.created_at from tasks t, user_tasks ut WHERE t.id = :id_task AND ut.task_id = t.id AND ut.user_id = :id_user_task", $data);
        if(count($tasks) > 0) return $tasks;
        else return NULL;
    }

    /**
     *Obtention de  tous les  tâches de l'utilisateur
     * @param String $user_id id de l'utilisateur
     * @return all tasks user
     */
    public function getAllUserTasks($user_id)
    {
        $data = array(
            "user_id" => $user_id
        );

        $tasks_user = $this->db->query("SELECT t.* FROM tasks t, user_tasks ut WHERE t.id = ut.task_id AND ut.user_id = :user_id", $data);

        if(count($tasks_user) > 0) return $tasks_user; // var_dump($tasks_user);
        else return NULL; //echo "Pas de tasks pour l'user ayant l'id : " . $user_id;
    }

    /**
     * Mise à jour de la tâche
     * @param String $task_id id de la tâche
     * @param String $task Le texte de la tâche
     * @param String $status le statut de la tâche
     * @return boolean
     */
    public function updateTask($user_id, $task_id, $task, $status)
    {
        $data = array(
            "task"      => $task,
            "status"    => $status,
            "task_id"   => $task_id,
            "user_id"   => $user_id
        );

        $update_task = $this->db->query("UPDATE tasks t, user_tasks ut SET t.task = :task, t.status = :status WHERE t.id = :task_id AND t.id = ut.task_id AND ut.user_id = :user_id", $data);

        if($update_task == 1 ) return TRUE;
        else return FALSE;
    }

    /**
     * Suppression d'une tâche
     * @param String $task_id id de la tâche à supprimer
     * @return boolean
     */
    public function deleteTask($user_id, $task_id)
    {
        $data = array(
            "task_id"   => $task_id,
            "user_id"   => $user_id
        );

        $delete_task = $this->db->query("DELETE t FROM tasks t, user_tasks ut WHERE t.id = :task_id AND ut.task_id = t.id AND ut.user_id = :user_id", $data);

        if($delete_task == 1 ) return TRUE;
        else return FALSE;
    }

}

//test
$tasks = new DbTasks();
//$id = '1';
//$statut = '0';
//$tasks->updateTask($id,$id,'Changement de taches encore',$statut);
//$tasks->deleteTask('1','1);
//$tasks->getAllUserTasks('2');
//$tasks->getTask('3', '2');
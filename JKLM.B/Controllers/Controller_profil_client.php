<?php
require_once "Controller.php";
# Controller pour le profil des clients 
class Controller_profil_client extends Controller{
    public function action_profil_client(){

        if (isset($_SESSION['idutilisateur']) && $_SESSION['role'] == "formateur") {
            header("Location: ?controller=form&action=form");
        }

        if (!isset($_SESSION['idutilisateur'])) {
            header("Location: ?controller=home&action=home");
        }



        $this->render("profil_client");

    }
    public function action_default(){
        $this->action_profil_client();
    }

public function action_send_message(){
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?controller=profil_client');
            exit();
        }

        $user = $this->checkAccess();

        $model = Model::getModel();

        $discussionId = isset($_POST['discussionId']) ? e($_POST['discussionId']) : null;

        if (!$discussionId) {
            header('Location: ?controller=profil_client');
            exit();
        }

        $discussion = $model->getDiscussionById($discussionId);

        if (!$discussion || !isUserInDiscussion($user['idutilisateur'], $discussion)) {
            header('Location: ?controller=profil_client');
            exit();
        }

        $texteMessage = isset($_POST['texte_message']) ? e($_POST['texte_message']) : '';

        $isAdmin = $model->verifAdmin($user['idutilisateur']);
        $isModo = $model->verifModerateur($user['idutilisateur']);
        $isAffranchi = $model->verifAffranchiModerateur($user['idutilisateur']);

        $validation_moderation = ($isAdmin || $isModo || $isAffranchi);

        $result = $model->addMessageToDiscussion($texteMessage, $discussion['id_utilisateur'],      $discussion['id_utilisateur_1'], $discussionId, $validation_moderation, $user   ['idutilisateur']);

        header('Location: ?controller=discussion&action=discussion&id=' . $discussionId);
        exit();
    }

    public function action_start_discussion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?controller=profil_client');
            exit();
        }

        $user = $this->checkAccess();

        $model = Model::getModel();

        $id_client = $user['idutilisateur'];
        $id_formateur = isset($_POST['id_formateur']) ? e($_POST['id_formateur']) : null;

        $discussion_id = $model->startDiscussion($id_client, $id_formateur);
        if (!$discussion_id) {
            header('Location: ?controller=profil_client');
            exit();
        }

        header('Location: ?controller=discussion&action=discussion&id=' . $discussion_id);
        exit();
    }

    private function checkAccess() {
        if (!isset($_SESSION['idutilisateur'])) {
            header("Location: ?controller=home&action=home");
            exit();
        }

        return $_SESSION;
    }
}
?>

<?php

class Controller_discussion extends Controller
{
    public function action_default()
    {
        $this->action_list();
    }

    public function action_list()
    {
        $user = $this->checkAccess();
        $role = getUserRole($user);
        $model = Model::getModel();
        //Ajout des permissions pour les admins et modérateurs
        $isAdmin = $model->verifAdmin($user['id_utilisateur']);
        $isModo = $model->verifModerateur($user['id_utilisateur']);

        $discussions = $model->recupererDiscussion($user['id_utilisateur']);
        $discussionList = [];

        foreach ($discussions as $discussion) {
            $interlocuteurId = ($role === 'Client') ? $discussion['id_utilisateur_1'] : $discussion['id_utilisateur'];
            $interlocuteur = $model->getUserById($interlocuteurId);
            if (!$interlocuteur) {
                continue;
            }
            $unreadMessages = $model->countUnreadMessages($interlocuteurId, $discussion['id_discussion']);
            $discussionList[] = [
                'discussion_id' => $discussion['id_discussion'],
                'nom_interlocuteur' => $interlocuteur['nom'],
                'prenom_interlocuteur' => $interlocuteur['prenom'],
                'photo_interlocuteur' => $interlocuteur['photo_de_profil'],
                'unread_messages' => ($unreadMessages > 0),
            ];
        }

        $data = [
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'photo_de_profil' => $user['photo_de_profil'],
            'role' => $role,
            'discussions' => $discussionList,
            'isModo' => $isModo,
            'isAdmin' => $isAdmin
        ];

        $this->render('discussion_list', $data);
    }

    public function action_discussion()
    {
        $user = $this->checkAccess();
        $model = Model::getModel();
        $discussionId = $this->getValidatedId('id');

        if (!$discussionId) {
            $this->redirect('discussion');
        }

        $isModo = $model->verifModerateur($user['id_utilisateur']);
        $discussion = $model->getDiscussionById($discussionId);

        if (!$discussion || !($isModo || isUserInDiscussion($user['id_utilisateur'], $discussion))) {
            $this->redirect('discussion');
        }

        $role = getUserRole($user);
        $receiverId = ($role === 'Client') ? $discussion['id_utilisateur_1'] : $discussion['id_utilisateur'];
        $receiver = $model->getUserById($receiverId);

        if (!$receiver) {
            $this->redirect('discussion');
        }

        $messages = $model->messagesDiscussion($discussionId);

        $data = [
            'nom_receiver' => $receiver['nom'],
            'prenom_receiver' => $receiver['prenom'],
            'photo_receiver' => $receiver['photo_de_profil'],
            'messages' => $messages,
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'photo_de_profil' => $user['photo_de_profil'],
            'role' => $role,
            'user_id' => $user['id_utilisateur'],
            'isModo' => $isModo
        ];

        $this->render('discussion', $data);
    }

    public function action_envoi_message()
    {
        $this->ensurePostMethod();

        $user = $this->checkAccess();
        $model = Model::getModel();
        $discussionId = $this->getValidatedPostParam('discussionId');

        if (!$discussionId) {
            $this->redirect('discussion');
        }

        $discussion = $model->getDiscussionById($discussionId);

        if (!$discussion || !isUserInDiscussion($user['id_utilisateur'], $discussion)) {
            $this->redirect('discussion');
        }

        $texteMessage = $this->getValidatedPostParam('texte_message');

        // ici on a beosin de verifier que l'utilisateur est un formateur et a besoin d'une validation de message 
        $isFormateur = getUserRole($user) === 'Formateur';
        $validation_moderation = false;

        if ($isFormateur) {
            // Par ici les formateurs on besoin que les moderateurs valides leurs messages
            $validation_moderation = true;
        } else {
            //pas besoin de validation des messages 
            $validation_moderation = false;
        }

        $model->addMessageToDiscussion($texteMessage, $discussion['id_utilisateur'], $discussion['id_utilisateur_1'], $discussionId, $validation_moderation, $user['id_utilisateur']);

        $this->redirect("discussion&action=discussion&id={$discussionId}");
    }

    public function action_start_discussion()
    {
        $this->ensurePostMethod();

        $user = $this->checkAccess();
        $model = Model::getModel();
        $id_formateur = $this->getValidatedPostParam('id_formateur');
        $discussion_id = $model->startDiscussion($user['id_utilisateur'], $id_formateur);

        if (!$discussion_id) {
            $this->redirect('discussion');
        }

        $this->redirect("discussion&action=discussion&id={$discussion_id}");
    }

    public function action_validate_message()
    {
        $user = $this->checkAccess();
        $model = Model::getModel();

        if (!$model->verifModerateur($user['id_utilisateur'])) {
            $this->redirect('discussion');
        }

        $id_message = $this->getValidatedId('id_message');
        if (!$id_message) {
            $this->redirect('discussion');
        }

        $discussion_id = $model->validateMessage($id_message);
        if (!$discussion_id) {
            echo "Erreur lors de la validation du message.";
            exit();
        }

        $this->redirect("discussion&action=discussion&id={$discussion_id}");
    }

    private function checkAccess()
    {
        $user = checkUserAccess();
        if (!$user) {
            echo "Accès non autorisé.";
            $this->render('auth', []);
            exit;
        }
        return $user;
    }

    private function getValidatedId($key)
    {
        return isset($_GET[$key]) ? e($_GET[$key]) : null;
    }

    private function getValidatedPostParam($key)
    {
        return isset($_POST[$key]) ? e($_POST[$key]) : null;
    }

    private function ensurePostMethod()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('discussion');
        }
    }

    private function redirect($controllerAction)
    {
        header("Location: ?controller={$controllerAction}");
        exit();
    }
}

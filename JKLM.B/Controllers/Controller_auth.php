<?php

class Controller_auth extends Controller
{
    public function action_auth()
    {
        $this->render("auth", []);
    }

    public function action_default()
    {
        $this->action_auth();
    }

    public function action_login()
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (isset($_POST['email'], $_POST['password']) && !empty($_POST['email']) && !empty($_POST['password'])) {
                $email = e(trim($_POST['email']));
                $password = e(trim($_POST['password']));

                if (strlen($password) <= 256 && strlen($email) <= 128) {
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $user = Model::getModel()->getUserByCredentials($email, $password);

                        if ($user) {

                            var_dump($user);

                            // Connexion réussie, créer une session et stocker le token
                            session_start();

                            // Vous pouvez stocker d'autres informations de l'utilisateur si nécessaire
                            $_SESSION['user_id'] = $user['id_utilisateur'];
                            $_SESSION['user_token'] = $user['token'];
                            $_SESSION['expire_time'] = time() + (30 * 60); // 15 minutes d'expiration

                            // Rediriger vers le tableau de bord après la connexion réussie
                            header("Location: ?controller=dashboard");
                            exit();
                        } else {
                            if (!Model::getModel()->utilisateurExiste($email)){
                                echo "Ce compte n'existe pas.";
                            }
                            else{
                                echo "Le mot de passe est incorrect.";
                            }
                        }
                    } else {
                        echo "Format d'e-mail invalide.";
                    }
                } else {
                    echo "Les données saisies dépassent les limites autorisées.";
                }
            } else {
                echo "Veuillez remplir tous les champs requis.";
            }
        } else {
            echo "Accès non autorisé.";
        }

        $this->render("auth", []);
    }

    public function action_register() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (
                isset($_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['password'], $_POST['tabs'])
                && !empty($_POST['nom']) && !empty($_POST['prenom']) && !empty($_POST['email']) && !empty($_POST['password'])
            ) {
                $nom = e(trim($_POST['nom']));
                $prenom = e(trim($_POST['prenom']));
                $email = e(trim($_POST['email']));
                $password = e(trim($_POST['password']));

                if (strlen($nom) <= 64 && strlen($prenom) <= 64 && strlen($password) <= 256 && strlen($email) <= 128) {
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        if (preg_match('/^[a-zA-Z\-]+$/', $nom) && preg_match('/^[a-zA-Z\-]+$/', $prenom)) {

                            $role = isset($_POST['tabs']) ? ($_POST['tabs'] === 'client' ? 'client' : ($_POST['tabs'] === 'formateur' ? 'formateur' : '')) : '';

                            if ($role !== '') {
                                $result = Model::getModel()->creationUtilisateur($nom, $prenom, $password, $email, $role);

                                if ($result) {
                                    echo "Inscription réussie!<br>";
                                    $verificationToken = Model::getModel()->getTokenUtilisateur($email);
                                    $verificationLink = 'http://localhost/perform_vision/?controller=auth&action=valide_email'. '&token=' . urlencode($verificationToken);

                                    EmailSender::sendVerificationEmail($email, 'Vérification de l\'adresse e-mail', 'Cliquez sur le lien suivant pour vérifier votre adresse e-mail: ' . $verificationLink);
                                    
                                    echo "<br> Un e-mail de vérification a été envoyé à votre adresse. <br>";

                                } else {
                                    echo "Erreur lors de l'inscription.";
                                }
                            } else {
                                echo "Rôle invalide.";
                            }
                        } else {
                            echo "Le nom et le prénom ne doivent contenir que des lettres et des tirets.";
                        }
                    } else {
                        echo "Format d'email invalide.";
                    }
                } else {
                    echo "Les données saisies dépassent les limites autorisées.";
                }
            } else {
                echo "Veuillez remplir tous les champs requis.";
            }
        } else {
            echo "Accès non autorisé.";
        }

        $this->render("auth", []);

    }

    public function action_valide_email() {
        // Récupérer le token depuis les paramètres de l'URL
        $token = isset($_GET['token']) ? $_GET['token'] : '';
    
        // Valider le token en appelant une fonction du modèle
        $validationResult = Model::getModel()->validerTokenUtilisateur($token);
    
        if ($validationResult) {
            echo "Adresse e-mail vérifiée avec succès!";
        } else {
            echo "Erreur lors de la vérification de l'adresse e-mail. Le lien peut avoir expiré ou être invalide.";
        }
    
        $this->render("auth", []);
    }
}

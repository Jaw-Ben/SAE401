<?php

class Controller_profile extends Controller
{
    public function action_default()
    {
        $this->action_profile();
    }

    public function action_profile()
    {

        $user = checkUserAccess();

        if (!$user) {
            echo "Accès non autorisé.";
            $this->render('auth', []);
        }

        $role = getUserRole($user);

        $model = Model::getModel();
        //Ajout des permissions pour les admins et modérateurs
        $isAdmin = $model->verifAdmin($user['id_utilisateur']);
        $isModo = $model->verifModerateur($user['id_utilisateur']);
        $data = [
            'mail' => $user['mail'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'photo_de_profil' => $user['photo_de_profil'],
            'role' => $role,
            'isModo' => $isModo,
            'isAdmin' => $isAdmin
        ];

        if ($role === 'Client') {
            $data['societe'] = $model->getClientById($user['id_utilisateur']);
            $this->render('monprofilclient', $data);
        } elseif ($role === 'Formateur') {
            $data['formateur'] = $model->getFormateurById($user['id_utilisateur']);
            $data['competences'] = $model->getCompetencesFormateurById($user['id_utilisateur']);
            $this->render('monprofilformateur', $data);
        } else {
            echo "Accès non autorisé.";
            $this->render('auth', []);
        }
    }

    public function action_modifier()
    {

        $user = checkUserAccess();

        if (!$user) {
            echo "Accès non autorisé.";
            $this->render('auth', []);
        }

        $role = getUserRole($user);

        $model = Model::getModel();
        $isAdmin = $model->verifAdmin($user['id_utilisateur']);
        $isModo = $model->verifModerateur($user['id_utilisateur']);

        $data = [
            'mail' => $user['mail'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'photo_de_profil' => $user['photo_de_profil'],
            'role' => $role,
            'isModo' => $isModo,
            'isAdmin' => $isAdmin
        ];

        if ($role === 'Client') {
            $data['societe'] = $model->getClientById($user['id_utilisateur']);
            $this->render('modifiermonprofilClient', $data);
        } elseif ($role === 'Formateur') {
            $data['formateur'] = $model->getFormateurById($user['id_utilisateur']);
            $data['competences'] = $model->getCompetencesFormateurById($user['id_utilisateur']);
            $this->render('modifiermonprofilformateur', $data);
        } else {
            echo "Accès non autorisé.";
            $this->render('auth', []);
        }
    }

    public function action_modifier_info(){

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?controller=profile');
            exit();
        }

        $user = checkUserAccess();

        if (!$user) {
            echo "Accès non autorisé.";
            $this->render('auth', []);
        }

        $role = getUserRole($user);

        $model = Model::getModel();

        if (isset($_POST['nouvelle_email']) && !empty($_POST['nouvelle_email']) && $_POST['nouvelle_email'] !== $user['mail'] && filter_var($_POST['nouvelle_email'], FILTER_VALIDATE_EMAIL)) {
            $nouvelle_email = $_POST['nouvelle_email'];
            $model->updateEmail($user['id_utilisateur'], $nouvelle_email);
        }

        if (isset($_POST['nouveau_mot_de_passe']) && !empty($_POST['nouveau_mot_de_passe'])) {
            $nouveau_mot_de_passe = e(trim($_POST['nouveau_mot_de_passe']));
            if (strlen($nouveau_mot_de_passe) <= 256) {
                $model->updatePassword($user['id_utilisateur'], $nouveau_mot_de_passe);
            }
        }

        if (isset($_POST['nouvelle_societe'])) {
            $nouvelle_societe = e(trim($_POST['nouvelle_societe']));
            if (!empty($nouvelle_societe) && $nouvelle_societe !== $model->getClientById($user['id_utilisateur'])['societe']) {
                $model->updateSociete($user['id_utilisateur'], $nouvelle_societe);
            }
        }

        if (isset($_POST['nouveau_linkedin'])) {
            $nouveau_linkedin = e(trim($_POST['nouveau_linkedin']));
            $ancien_linkedin = $model->getFormateurById($user['id_utilisateur'])['linkedin'];
        
            if (!empty($nouveau_linkedin) && $nouveau_linkedin !== $ancien_linkedin) {
                $model->updateLinkedIn($user['id_utilisateur'], $nouveau_linkedin);
            }
        }
        
        /**
         * Vérifier le fichier
         * Créer un id pour le cv
         * Le déplacer dans répertoire cv
         * Rajouter le cv dans la base de données
         */
        if (isset($_FILES["nouveau_cv"]) && $_FILES["nouveau_cv"]["error"] == 0){
            $cv_id = uniqid();
            move_uploaded_file($_FILES['nouveau_cv']['tmp_name'], "Content/cv/" . $cv_id . ".pdf");
            $model->creationCV($user['id_utilisateur'],$cv_id);
        }
        
        header('Location: ?controller=profile');
        exit();

    }

    public function ajouter_competence() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            
            $skillName = $_POST['skillName'];
            $skillSpecialty = $_POST['skillSpecialty'];
            $skillLevel = $_POST['skillLevel'];

            try {    
                // Vous devez obtenir l'ID du thème en fonction de son nom et de sa catégorie
                $stmt = $pdo->prepare("SELECT id_theme FROM theme WHERE nom_theme = :nom_theme AND id_categorie IN (SELECT id_categorie FROM categorie WHERE nom_categorie = :nom_categorie)");
                $stmt->bindParam(':nom_theme', $skillName);
                $stmt->bindParam(':nom_categorie', $skillSpecialty);
                $stmt->execute();
                $themeRow = $stmt->fetch(PDO::FETCH_ASSOC);
                $themeId = $themeRow['id_theme'];
    
                // Insérer les compétences dans la table aExpertiseProfessionnelle
                $stmt = $pdo->prepare("INSERT INTO aExpertiseProfessionnelle (id_utilisateur, id_theme, duree_experience, id_niveau) VALUES (:id_utilisateur, :id_theme, :duree_experience, :id_niveau)");
                $stmt->bindParam(':id_utilisateur', $_SESSION['id_utilisateur']);
                $stmt->bindParam(':id_theme', $themeId);
                $stmt->bindValue(':duree_experience', 0); // À remplir avec la durée d'expérience appropriée du formateur
                $stmt->bindParam(':id_niveau', $skillLevel);
                $stmt->execute();
    
                // Vous pouvez envoyer une réponse JSON si nécessaire
                echo json_encode(['success' => true]);
                exit();
            } catch (PDOException $e) {
                // Gérer les erreurs éventuelles
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                exit();
            }
        }
    }
}

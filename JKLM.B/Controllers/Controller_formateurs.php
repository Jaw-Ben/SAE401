<?php

class Controller_formateurs extends Controller
{
    public function action_default()
    {
        $this->action_formateurs();
    }

    public function action_formateurs()
    {

        $user = checkUserAccess();

        if (!$user) {
            echo "Accès non autorisé.";
            $this->render('auth', []);
        }

        $role = getUserRole($user);

        $page = 1;
        if (isset($_GET["page"]) and preg_match("/^\d+$/", $_GET["page"]) and $_GET["page"] > 0) {
            $page = e($_GET["page"]);
        }



        $search = isset($_POST['search']) ? trim(e($_POST['search'])) : '';

        $model = Model::getModel();

        $categorieId = isset($_POST['select-options']) ? $_POST['select-options'] : null;

        $themes = isset($_POST['selected-themes']) ? $_POST['selected-themes'] : null;

        $themeId = null;
        //$categorieId = $model->getCategorieIdByName($search);
        //$themeId = $model->getThemeIdByName($search);

        //echo $categorieId .' -';
        //var_dump($model->getFormateursBasicInfoByPageAndCategoryOrTheme3($page, $categorieId, $themes));
        //var_dump($model->getCategoryAndThemesByUserAndCategory($formateurs[0]['id_utilisateur'], $categorieId));
        //echo $categorieId .' -';
        //echo $model->getNbFormateurByThemesOrCategorie3($themes, $categorieId);

        $formateurs = $model->getFormateursBasicInfoByPageAndCategoryOrTheme3($page, $categorieId, $themes);//$model->getFormateursBasicInfoByPageAndCategoryOrTheme2($page, $categorieId, $themeId);
        $nbFormateurs = $model->getNbFormateurByThemeOrCategorie3($themes, $categorieId);//$model->getNbFormateurByThemeOrCategorie($themeId, $categorieId);
        $nb_total_pages = $model->getFormateurPagesByCategoryOrTheme3($categorieId, $themeId);
        //Ajout des permissions pour les admins et modérateurs
        $isAdmin = $model->verifAdmin($user['id_utilisateur']);
        $isModo = $model->verifModerateur($user['id_utilisateur']);
        //echo $formateurs[0]['id_utilisateur'];
       // var_dump($model->getUserCategoryAndThemes($formateurs[0]['id_utilisateur'], $categorieId));

       if($formateurs !== null){
            foreach ($formateurs as &$formateur) {
                $userCategoryAndThemes = $model->getUserCategoryAndThemes($formateur['id_utilisateur'], $categorieId);
                //var_dump($userCategoryAndThemes);
                if ($userCategoryAndThemes !== null) {
                    // Add category and theme information to the formateur
                    $formateur['category_name'] = $userCategoryAndThemes['nom_categorie'];
                    $formateur['theme_names'] = $userCategoryAndThemes['theme_names'];
                    $formateur['expertise_comment'] = $userCategoryAndThemes['expertise_comment'];
                } else {
                    // Set default values if no information is found
                    $formateur['category_name'] = 'N/A';
                    $formateur['theme_names'] = [];
                    $formateur['expertise_comment'] = [];
                }
            }
       }


        //Détermination du début et de la fin des numéros de page à afficher
        $debut = $page - 5;
        if($debut <= 0 ){
            $debut = 1;
        }
        
        $fin = $debut + 9;
        if($fin > $nb_total_pages){
            $fin = $nb_total_pages;
        }

        $data = [
                'selectedThemes' => $themes,
                'selectedCategoryId' => $categorieId,
                 'categories' => $model->getAllCategories(),
                 'themes' => $model->getThemesByCategoryId($categorieId),
                 'formateurs' => $formateurs,
                 'active' => $page,
                 'debut' => $debut,
                 'fin' => $fin,
                 'nb_total_pages' => $nb_total_pages,
                 'nom' => $user['nom'],
                 'prenom' => $user['prenom'],
                 'photo_de_profil' => $user['photo_de_profil'],
                 'role' => $role,
                 'isModo' => $isModo,
                 'isAdmin' => $isAdmin
                ];

        $this->render('formateurs', $data);
    }

    public function action_details(){

        $user = checkUserAccess();

        if (!$user) {
            echo "Accès non autorisé.";
            $this->render('auth', []);
        }

        $role = getUserRole($user);

        $modele = Model::getModel();

        $id_formateur = isset($_GET['id']) ? e($_GET['id']) : null;

        $formateur = $modele->getUserById($id_formateur);

        $niveaux = $modele->getLevelDataById($id_formateur);
        
        $pedagogicalExperience = $modele->getPedagogicalExperienceDataById($id_formateur);
        
        $categories = $modele->getCategorieDataById($id_formateur);

        $themes = $modele->getThemeDataById($id_formateur);
        
        $expertises = $modele->getExpertiseDataById($id_formateur);
        
        $this->render('formateurs_details', [
            'formateur' => $formateur,
            'niveaux' => $niveaux,
            'pedagogicalExperience' => $pedagogicalExperience,
            'categories' => $categories,
            'themes' => $themes,
            'expertises' => $expertises,
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'photo_de_profil' => $user['photo_de_profil'],
            'role' => $role
        ]);
    }
}
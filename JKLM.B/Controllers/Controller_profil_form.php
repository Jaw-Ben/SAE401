<?php
require_once "Controller.php";
# Controller pour le profil des formateurs
class Controller_profil_form extends Controller{
    public function action_profil_form(){

        if (isset($_SESSION['idutilisateur']) && $_SESSION['role'] == "client") {
            header("Location: ?controller=client&action=client");
        }

        if (!isset($_SESSION['idutilisateur'])) {
            header("Location: ?controller=home&action=home");
        }

        $this->render("profil_form");

    }
    public function action_default(){
        $this->action_profil_form();
    }
}
?>
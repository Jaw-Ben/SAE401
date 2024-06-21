<?php

class Controller_logout extends Controller
{

    public function action_default()
    {
        $this->action_logout();
    }


    public function action_logout(){

        // Démarre une session si y'en a pas
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    

        // Supprime toutes les session ouvertes
        session_unset();

        // Détruit la session
        session_destroy();

        // Redirection vers la page d'accueil
        header("Location: index.php");
        exit();
    }
}


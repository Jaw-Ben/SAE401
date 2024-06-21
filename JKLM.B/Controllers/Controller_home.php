<?php

class Controller_home extends Controller
{
    public function action_default()
    {
        $this->action_home();
    }

    public function action_home(){

        $model = Model::getModel();

        $data = [
            'activites' => $model->getActivitiesList()
        ];

        $this->render('home', $data);

    }

    public function action_activite(){

        $model = Model::getModel();

        $id_activite = isset($_GET['id']) ? e($_GET['id']) : null;
        if(!$id_activite){
            $data = [
                'activites' => $model->getActivitiesList()
            ];
            $this->render('home', $data);
            return;
        }

        $data = [
            'activite' => $model->getActivityById($id_activite)
        ];

        $this->render('activite', $data);

    }

}
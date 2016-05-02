<?php
/* Copyright 2016 Zachary Doll */

class GitBotController extends Gdn_Controller {

  private $secret = '77f13866b4164c0c45d35f76d92339210db5d5e78fba9756f8cacd7f0f940849';
  
  public function Initialize() {
    parent::Initialize();
  }

  public function index() {
    $this->renderData(['success' => true]);
  }
  
  public function pullRequest() {
    $requestBody = file_get_contents('php://input');
    $data = json_decode($requestBody);

    if(property_exists($data, 'action') && $data->action == 'opened') {
        $pr = property_exists($data, 'pull_request') ? $data->pull_request : false;
        if($pr) {
            $user = property_exists($pr, 'user') ? $data->pull_request : false;
            if($user) {
                Logger::log(Logger::INFO, $user->login);
            }
        }
    }
    
    $this->index();
  }

}

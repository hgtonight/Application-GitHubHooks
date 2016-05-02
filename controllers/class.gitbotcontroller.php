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

    $gitHubName = $this->getPullRequestSubmitterName($data);
    Logger::log(Logger::INFO, 'GitHubName', (array)$gitHubName);
    
    $userModel = new UserModel();
    $user = $userModel->getByUsername($gitHubName);
    
    Logger::log(Logger::INFO, 'VanillaUser', (array)$user);
    $this->index();
  }

  private function getPullRequestSubmitterName($data) {
    $name = null;
    if(property_exists($data, 'action') && $data->action == 'opened') {
      $pr = property_exists($data, 'pull_request') ? $data->pull_request : false;
      if($pr) {
        $user = property_exists($pr, 'user') ? $pr->user : false;
        $name = (property_exists($user, 'login')) ? $user->login : null;
      }
    }
    return $name;
  }
}

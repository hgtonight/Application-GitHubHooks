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
    $signed = !!val('DateContributorAgreement', $user);
    
    $this->renderData(['signed' => $signed]);
  }

  private function getPullRequestSubmitterName($data) {
    $name = null;
    $action = val('action', $data);
    if($action == 'opened') {
      $pr = val('pull_request', $data);
      if($pr) {
        $name = valr('user.login', $pr);
      }
    }
    return $name;
  }
}

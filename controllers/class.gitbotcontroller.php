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
    $data = json_decode(file_get_contents('php://input'));

    $gitHubName = $this->getPullRequestSubmitterName($data);
    
    $userModel = new UserModel();
    $user = $userModel->getByUsername($gitHubName);
    
    $signed = !!val('DateContributorAgreement', $user);
    
    $existingComments = json_decode(file_get_contents('https://api.github.com/repos/vanilla/vanilla/pulls/1/comments'));
    
    Logger::log(Logger::INFO, 'ExistingComments', (array)$existingComments);
    
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

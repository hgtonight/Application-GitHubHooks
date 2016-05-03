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
    
    $this->commentOnSignedStatus($data, $signed);
    
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
  
  private function commentOnSignedStatus($data, $alreadySigned) {
    require_once(PATH_APPLICATIONS . '/githubhooks/library/client/GitHubClient.php');
    $body = "It doesn't appear that you have signed the CLA.";
    if($alreadySigned) {
        $body = "It appears you have already signed the CLA.";
    }
    
    $issue = valr('pull_request.number', $data, false);
    Logger::log(Logger::INFO, 'Issue Number', (array)$issue);
    if($issue) {
      $client = new GitHubClient();
      $client->setCredentials($username, $password);
      $client->issues->createComment('vanilla', 'vanilla', $issue, $body);
    }
  }
}

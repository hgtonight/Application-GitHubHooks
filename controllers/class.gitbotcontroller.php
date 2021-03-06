<?php
/* Copyright 2016 Zachary Doll */

class GitBotController extends Gdn_Controller {

  public function Initialize() {
    parent::Initialize();
  }

  public function index() {
    $this->renderData(['success' => true]);
  }
  
  public function pullRequest() {
    $payload = file_get_contents('php://input');
    if(!$this->verifySignature($payload)) {
      Logger::event('SignatureInvalid', Logger::EMERGENCY, 'HMAC does not match!');
      $this->renderData(['hookReceived' => false, 'error' => 'Invalid Signature']);
      return;
    }
    
    $data = json_decode($payload);

    $action = val('action', $data);
    switch($action) {
      case 'opened':
        $this->pullRequestOpened($data);
        break;
      default:
        $this->renderData(['hookReceived' => true]);
        break;
    }
  }
  
  private function verifySignature($payload) {
    $secret = c('GitHubHooks.PullRequestSecret');
    $expected = Gdn::request()->getValue('HTTP_X_HUB_SIGNATURE');
    $calculated = 'sha1=' . hash_hmac('sha1', $payload , $secret);
    return compareHashDigest($expected, $calculated);
  }
  
  private function pullRequestOpened($data) {
    $gitHubName = $this->getPullRequestSubmitterName($data);
    
    $userModel = new UserModel();
    $user = $userModel->getByUsername($gitHubName);
    
    $signed = !!val('DateContributorAgreement', $user);
    
    $this->commentOnSignedStatus($data, $signed, $gitHubName);
    
    $this->renderData(['hookReceived' => true, 'claSigned' => $signed]);
  }

  private function getPullRequestSubmitterName($data) {
    $name = null;
    $pr = val('pull_request', $data);
    if($pr) {
      $name = valr('user.login', $pr);
    }
    
    return $name;
  }
  
  private function commentOnSignedStatus($data, $alreadySigned, $name) {
    $body = '';
    if($alreadySigned) {
        $body .= sprintf(t("**%s** appears to have already signed the contributor's agreement."), $name);
    }
    else {
        $body .= sprintf(t("Can you sign our contributor's agreement @%s? http://vanillaforums.org/contributors"), $name);
    }
    
    $issue = valr('pull_request.number', $data);
    $repoOwner = valr('repository.owner.login', $data);
    $repoName = valr('repository.name', $data);
    
    if($issue && $repoOwner && $repoName) {
      require_once(PATH_APPLICATIONS . '/githubhooks/library/client/GitHubClient.php');
      $client = new GitHubClient();
      $client->setAuthType(GitHubClient::GITHUB_AUTH_TYPE_OAUTH);
      $client->setOauthToken(c('GitHubHooks.OAuthToken'));
      $client->issues->comments->createComment($repoOwner, $repoName, $issue, $body);
    }
  }
}

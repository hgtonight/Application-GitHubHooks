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
      $this->renderData(['error' => 'invalid request']);
      return;
    }
    
    $data = json_decode($payload);

    $action = val('action', $data);
    if($action !== 'opened') {
      return;
    }
    
    $gitHubName = $this->getPullRequestSubmitterName($data);
    
    $userModel = new UserModel();
    $user = $userModel->getByUsername($gitHubName);
    
    $signed = !!val('DateContributorAgreement', $user);
    
    $this->commentOnSignedStatus($data, $signed, $gitHubName);
    
    $this->renderData(['signed' => $signed]);
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
    require_once(PATH_APPLICATIONS . '/githubhooks/library/client/GitHubClient.php');
    $token = c('GitHubHooks.OAuthToken');
    $repoOwner = c('GitHubHooks.RepoOwner');
    $repoName = c('GitHubHooks.RepoName');
    $body = '';
    if($alreadySigned) {
        $body .= sprintf(t("**%s** appears to have already signed the contributor's agreement."), $name);
    }
    else {
        $body .= sprintf(t("Can you sign our contributor's agreement @%s? http://vanillaforums.org/contributors"), $name);
    }
    
    $issue = valr('pull_request.number', $data, false);
    Logger::log(Logger::INFO, 'Issue Number', (array)$issue);
    if($issue) {
      $client = new GitHubClient();
      $client->setAuthType(GitHubClient::GITHUB_AUTH_TYPE_OAUTH);
      $client->setOauthToken($token);
      $client->issues->comments->createComment($repoOwner, $repoName, $issue, $body);
    }
  }
  
  private function verifySignature($payload) {
    $secret = c('GitHubHooks.PullRequestSecret');
    $expected = Gdn::request()->getValue('HTTP_X_HUB_SIGNATURE');
    $calculated = 'sha1=' . hash_hmac('sha1', $payload , $secret);
    return compareHashDigest($expected, $calculated);
  }
}

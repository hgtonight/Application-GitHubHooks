<?php
/* Copyright 2016 Zachary Doll */
$ApplicationInfo['GitHubHooks'] = array(
    'Name'        => 'GitHub Hooks',
    'Description' => 'A garden application that provides endpoints for various GitHub webhooks.',
    'Version'     => '0.1',
    'Url'         => 'http://github.com/hgtonight/application-githubhooks',
    'Author'      => 'Zachary Doll',
    'AuthorEmail' => 'hgtonight@daklutz.com',
    'AuthorUrl'   => 'https:/daklutz.com',
    'License'     => 'GPLv2',
    'SettingsUrl' => '/githubhooks/settings',
    'SettingsPermission' => 'Garden.Settings.Manage',

    // Application requirements
    'RequiredApplications' => array('Vanilla' => '2.2'),
);
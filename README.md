# MWSeAT
MediaWiki plugin that links to SeAT Authentication

Add these lines to the bottom of your LocalSettings.php

```
require_once('extensions/Auth_SeAT.php');
 
$wgAuth_Config['api_url'] = 'http://myseatapiurl/api/v1/authenticate';
$wgAuth_Config['api_user'] = 'mediawiki_auth';
$wgAuth_Config['api_pass'] = 'mypassword';
 
$wgAuth = new Auth_SeAT($wgAuth_Config);
```

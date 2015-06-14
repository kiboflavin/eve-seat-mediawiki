# MWSeAT
MediaWiki plugin for <a href="https://github.com/eve-seat/seat">EVE-SeAT</a> Authentication

Add these lines to the bottom of your LocalSettings.php

```
require_once('extensions/Auth_SeAT.php');
 
$wgAuth_Config['api_url'] = 'https://myseatapiurl/api/v1/authenticate';
$wgAuth_Config['api_user'] = 'mediawiki_auth';
$wgAuth_Config['api_pass'] = 'mypassword';

$wgAuth = new Auth_SeAT($wgAuth_Config);
```

The SeAT API requires external requests to come via https, so make sure you have an SSL certificate setup.

Also make sure the "Source Access IP" attached to the SeAT API user matches the IP that your server is sending the requests from.

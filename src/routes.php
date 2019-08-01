<?php 

Route::group(array('namespace' => '\Megaads\SsoPostback\Controllers'), function() {
    Route::any('/sso/postback', 'SsoPostbackController@ssoPostback');
});
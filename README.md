# SSO Postback for Laravel 5
This package support auto create user.

Install this package using composer

    ```
    composer require megaads/sso-postback
    ```

After install completed. Register service provider in configs/app.php.

    ```
    Megaads\SsoPostback\SsoPostbackServiceProvider::class
    ```

Then publish file config `sso-postback.php` to configs folder by command: 

    ```
    php artisan vendor:publish --provider="Megaads\SsoPostback\SsoPostbackServiceProvider"
    ```

Default route postback after install this package is `/sso/postback`.

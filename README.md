# Slim 2FA

This very simple example Slim Framework application implements 2FA using Google
Authenticator.


## Get going

* Copy `data/2fa.db.dist` to `data/2fa.db`
* `php -S 0.0.0.0:8888 public/index.php`
* Navigate to http://localhost:88888
  
## Usage

* Login using `rob`/`password`
* Register the QR code with Google Authenticator & enter the confirmation code
* Logout
* Login again using `rob`/`password`
* Entire Google Authenticator code in order to complete the login process.

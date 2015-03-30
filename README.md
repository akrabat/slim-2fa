# Slim 2FA

This very simple example Slim Framework application implements 2FA using Google
Authenticator.


## Get going

* `composer install`
* Copy `data/2fa.db.dist` to `data/2fa.db`
* `php -S 0.0.0.0:8888 -t public/ public/index.php`
* Navigate to http://localhost:8888
  
## Usage

* Login using `rob`/`password`
* Register the QR code with Google Authenticator & enter the confirmation code
* Logout
* Login again using `rob`/`password`
* Enter the Google Authenticator code in order to complete the login process.


## Exercises for the reader

In a real app you should add the following:

* Prevent brute force attacks on the login form and the Authenticator code form.
* Consider adding a "remember this browser" feature to the 2FA form for user convienience.

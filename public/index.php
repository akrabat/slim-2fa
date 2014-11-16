<?php
require __DIR__ . '/../vendor/autoload.php';

session_cache_limiter(false);
session_start();

// Prepare app
$app = new \Slim\Slim([
    'mode' => 'development',
    'templates.path' => __DIR__ . '/../templates',
    'db' => [
        'dsn' => 'sqlite:' . realpath(__DIR__ . '/../data/2fa.db'),
    ],
]);

// Set up resource locator
$app->container->singleton('log', function () {
    $log = new \Monolog\Logger('2fa-example');
    $log->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__ . '/../logs/app.log', \Monolog\Logger::DEBUG));
    return $log;
});

$app->container->singleton('db', function () use ($app) {
    $config = $app->config('db');
    $pdo = new PDO($config['dsn']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
});

$app->container->singleton('userMapper', function () use ($app) {
    return new \RKA\UserMapper($app->db);
});

// Prepare view
$app->view(new \Slim\Views\Twig());
$app->view->parserOptions = array(
    'charset' => 'utf-8',
    'cache' => $app->mode == 'development' ? false : realpath('../templates/cache'),
    'auto_reload' => true,
    'strict_variables' => false,
    'autoescape' => true,
    'debug' => true,
);
$app->view->parserExtensions = array(new \Slim\Views\TwigExtension());
$app->view->getInstance()->addExtension(new Twig_Extension_Debug());

// Define routes
$app->get('/', function () use ($app) {
    // Sample log message
    $app->log->info("'/' route");
    
    $app->render('index.twig', [
        'user' => isset($_SESSION['user']) ? $_SESSION['user'] : null,
    ]);
});

$app->get('/logout', function () use ($app) {
    session_unset();
    session_regenerate_id();
    $app->redirect('/');
});

$app->get('/login', function () use ($app) {
    $app->render('login.twig');
});

$app->post('/login', function () use ($app) {
    // log out the user first
    $_SESSION['user'] = null;
    session_regenerate_id();

    // Authenticate username & password
    $username = $app->request->post('username');
    $password = $app->request->post('password');

    $mapper = $app->userMapper;
    $user = $mapper->load($username);
    if ($user) {
        $valid = password_verify($password, $user->getPassword());
        if ($valid) {
            if ($user->getSecret()) {
                $_SESSION['user_in_progress'] = $user;
                $app->redirect('/auth2fa');
            }
            $_SESSION['user'] = $user;
            $app->redirect('/setup2fa');
        }
    }

    $app->flash('error', 'Failed to log in');
    $app->redirect('/login');
});

$app->get('/setup2fa', function () use ($app) {
    $user = $_SESSION['user'];
    $g    = new \Google\Authenticator\GoogleAuthenticator();
    
    // invent a secret for this user
    $secret = $g->generateSecret();
    $app->flash('secret', $secret);

    // Create a QR code via Google charts. The data to encode (chl) is:
    //      otpauth://totp/{label}?secret={secret}
    // where:
    //      label = {hostname}:{username}
    //
    // (see https://code.google.com/p/google-authenticator/wiki/KeyUriFormat)
    $data = sprintf("otpauth://totp/%s%%3A%s%%3Fsecret%%3D%s", $_SERVER['HTTP_HOST'], $user->getUsername(), $secret);
    $qrCodeUrl = "https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=" . $data;

    $app->render('setup2fa.twig', [
        'user'      => $_SESSION['user'],
        'secret'    => $secret,
        'qrCodeUrl' => $qrCodeUrl,
    ]);
});

$app->post('/setup2fa', function () use ($app) {
    $secret = $app->environment['slim.flash']['secret'];
    $time   = floor(time() / 30);
    $code   = $app->request->post('code');

    $g = new \Google\Authenticator\GoogleAuthenticator();
    if ($g->checkCode($secret, $code)) {
        // code is valid - store into user record
        $user = $_SESSION['user'];
        $user->setSecret($secret);

        $mapper = $app->userMapper;
        $mapper->save($user);
        
        $app->flash('message', 'Successfully set up two factor authentication!');
        $app->redirect('/');
    }
    $app->flash('error', 'Failed to confirm code');
    $app->redirect('/setup2fa');

});

$app->get('/auth2fa', function () use ($app) {
    $user = $_SESSION['user_in_progress'];
    $app->render('auth2fa.twig');
});

$app->post('/auth2fa', function () use ($app) {
    $user   = $_SESSION['user_in_progress'];
    $secret = $user->getSecret();
    $time   = floor(time() / 30);
    $code   = $app->request->post('code');

    $g = new \Google\Authenticator\GoogleAuthenticator();
    if ($g->checkCode($secret, $code)) {
        // code is valid!
        $_SESSION['user'] = $_SESSION['user_in_progress'];
        unset($_SESSION['user_in_progress']);
        $app->flash('message', 'Successfully logged in using two factor authentication!');
        $app->redirect('/');
    }

    $app->flash('error', 'Failed to confirm code');
    $app->redirect('/auth2fa');
});

// Run app
$app->run();

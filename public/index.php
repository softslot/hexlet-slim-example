<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;
use App\UserValidator;
use App\UserRepository;

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$courses = ['PHP', 'Java', 'GO'];
$users = ['mike', 'mishel', 'adel', 'keks', 'kamila'];

$app->get('/', function ($request, $response) {
    return $response->write('Welcome to Slim!');
})->setName('home');

$app->get('/users', function ($request, $response) use ($users) {
    $search = $request->getQueryParam('search');
    $filteredUsers = array_filter($users, fn($user) => str_contains($user, $search));
    $params = ['users' => $filteredUsers];

    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
})->setName('users');

$router = $app->getRouteCollector()->getRouteParser();

$app->get('/users/new', function ($request, $response) {
    $params = [
        'errors' =>
            ['nickname' => ''],
            ['email' => '']
        ];

    return $this->get('renderer')->render($response, 'users/new.phtml', $params);
});

$app->post('/users', function ($request, $response) use ($router) {
    $user = $request->getParsedBodyParam('user');
    $user['id'] = uniqid();
    $errors = UserValidator::validate($user);
    if (count($errors) > 0) {
        $params = ['user' => $user, 'errors' => $errors];
        return $this->get('renderer')->render($response, 'users/new.phtml', $params);
    }

    UserRepository::save($user);

    return $response->withRedirect($router->urlFor('users'), 302);
});

$app->get('/users/{id}', function ($request, $response, $args) {
    $params = ['id' => $args['id'], 'nickname' => 'user-' . $args['id']];

    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
});

$app->get('/courses', function ($request, $response) use ($courses) {
    $params = [
        'courses' => $courses
    ];
    return $this->get('renderer')->render($response, 'courses/index.phtml', $params);
});

$app->get('/courses/{id}', function ($request, $response, array $args) {
    $id = $args['id'];
    return $response->write("Course id: {$id}");
});

$app->run();

<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;
use App\UserValidator;
use App\UserRepository;

$repo = new UserRepository();

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$courses = ['PHP', 'Java', 'GO'];

$app->get('/', function ($request, $response) {
    return $response->write('Welcome to Slim!');
})->setName('home');

$app->get('/users', function ($request, $response) use ($repo) {
    $users = $repo->getAllUsers();
    $search = $request->getQueryParam('search');
    $filteredUsers = array_filter($users, function ($user) use ($search) {
        return str_contains(mb_strtolower($user['nickname']), mb_strtolower($search));
    });

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

$app->post('/users', function ($request, $response) use ($router, $repo) {
    $user = $request->getParsedBodyParam('user');
    $user['id'] = uniqid();
    $errors = UserValidator::validate($user);
    if (count($errors) > 0) {
        $params = ['user' => $user, 'errors' => $errors];
        return $this->get('renderer')->render($response, 'users/new.phtml', $params);
    }

    $repo->save($user);

    return $response->withRedirect($router->urlFor('users'), 302);
});

$app->get('/users/{id}', function ($request, $response, $args) use ($repo) {
    $user = $repo->getUserById($args['id']);
    if (empty($user)) {
        return $response->withStatus(404)
            ->withHeader('Content-Type', 'text/html')
            ->write('Page not found');
    }

    $params = ['user' => $user];

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

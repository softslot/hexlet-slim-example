<?php

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Middleware\MethodOverrideMiddleware;
use DI\Container;
use App\UserValidator;
use App\UserRepository;

session_start();

$container = new Container();
$container->set('renderer', fn() => new \Slim\Views\PhpRenderer(__DIR__ . '/../templates'));
$container->set('flash', fn() => new \Slim\Flash\Messages());

$app = AppFactory::createFromContainer($container);
$app->add(MethodOverrideMiddleware::class);
$app->addErrorMiddleware(true, true, true);

$courses = ['PHP', 'Java', 'GO'];

$app->get('/', function ($request, $response) {
    return $response->write('Welcome to Slim!');
})->setName('home');

$app->get('/users', function ($request, $response) {
    $repo = new UserRepository();
    $users = $repo->all();
    $search = $request->getQueryParam('search');
    $filteredUsers = array_filter($users, function ($user) use ($search) {
        return str_contains(mb_strtolower($user['nickname']), mb_strtolower($search));
    });

    $messages = $this->get('flash')->getMessages();
    $params = ['users' => $filteredUsers, 'messages' => $messages];

    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
})->setName('users');

$app->get('/users/{id}/edit', function ($request, $response, $args) {
    $repo = new UserRepository();
    $user = $repo->find($args['id']);
    if (empty($user)) {
        return $response->withStatus(404)
            ->withHeader('Content-Type', 'text/html')
            ->write('Page not found');
    }

    $params = [
        'user' => $user,
        'errors' => ['nickname' => ''], ['email' => '']
    ];

    return $this->get('renderer')->render($response, 'users/edit.phtml', $params);
})->setName('editUser');

$router = $app->getRouteCollector()->getRouteParser();

$app->patch('/users/{id}', function ($request, $response, $args) use ($router) {
    $repo = new UserRepository();
    $updatedUser = $request->getParsedBodyParam('user');
    $errors = UserValidator::validate($updatedUser);
    if (count($errors) > 0) {
        $params = ['user' => $updatedUser, 'errors' => $errors];
        return $this->get('renderer')->render($response, 'users/edit.phtml', $params);
    }

    $user = $repo->find($args['id']);
    if (empty($user)) {
        return $response->withStatus(404)
            ->withHeader('Content-Type', 'text/html')
            ->write('Page not found');
    }

    $user['nickname'] = $updatedUser['nickname'];
    $user['email'] = $updatedUser['email'];
    $repo->save($user);
    $this->get('flash')->addMessage('success', 'User was added successfully');

    return $response->withRedirect($router->urlFor('users'), 302);
});

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
    $errors = UserValidator::validate($user);
    if (count($errors) > 0) {
        $params = ['user' => $user, 'errors' => $errors];
        return $this->get('renderer')->render($response, 'users/new.phtml', $params);
    }

    $repo = new UserRepository();
    $repo->save($user);
    $this->get('flash')->addMessage('success', 'User was added successfully');

    return $response->withRedirect($router->urlFor('users'), 302);
});

$app->get('/users/{id}', function ($request, $response, $args) {
    $repo = new UserRepository();
    $user = $repo->find($args['id']);
    if (empty($user)) {
        return $response->withStatus(404)
            ->withHeader('Content-Type', 'text/html')
            ->write('Page not found');
    }

    $params = ['user' => $user];

    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
});

$app->delete('/users/{id}', function ($request, $response, $args) use ($router) {
    $id = $args['id'];
    $repo = new UserRepository();
    $repo->destroy($id);
    $this->get('flash')->addMessage('success', 'User has been deleted');

    return $response->withRedirect($router->urlFor('users'), 302);
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

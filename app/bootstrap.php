<?php
require_once __DIR__.'/../vendor/autoload.php';

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use Tekniktest\Repository;

$app = new Silex\Application();

$app['debug'] = true;

// DB
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_sqlite',
        'path'     => __DIR__.'/../db/app.db',
    )
));

// Twig templates
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views'
    )
);

// Support json request body
$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

// setup and share program repository
$app['programs.repository'] = $app->share(function($app) {
    return new Tekniktest\Repository\ProgramRepository($app['db']);
});

// render index page
$app->get('/', function() use($app) { 
    return $app['twig']->render('index.html');
});

// get all programs in xml format
$app->get('/programs.xml', function (Application $app, Request $request) {

    $programs = $app['programs.repository']->findAll();

    return new Response($app['twig']->render(
            'programs.xml',
            array('data' => $programs)
        ),
        200,
        array('Content-Type' => 'application/xml')
    );
});

// Api 

// get all programs
$app->get('/api/programs', function (Application $app, Request $request) {
    // call repository and find all programs
    return $app->json($app['programs.repository']->findAll());
});

// get program by id
$app->get('/api/programs/{id}', function (Application $app, Request $request, $id) {
    // call repository and find program by id
    return $app->json($app['programs.repository']->findById($id));
});

// delete program by id
$app->delete('/api/programs/{id}', function (Application $app, Request $request, $id) {
    // call repository and delete program by id
    return $app->json($app['programs.repository']->deleteById($id));
});

// create a new program
$app->post('/api/programs', function (Application $app, Request $request) {
	
	$program = array(
        'date' =>  $request->request->get('date'),
        'start_time' => $request->request->get('startTime'),
        'leadtext' =>  $request->request->get('leadtext'),
        'name' =>  $request->request->get('name'),
        '[b-line]' =>  $request->request->get('bLine'),
        'synopsis' => $request->request->get('synopsis'),
        'url' => $request->request->get('url')
    );

    $id = $app['programs.repository']->save($program);

	return $app->json(array('id' => $id));	
});

return $app;
<?php
//
// Application entry point.
// Use phpinfo(); to see details about php version
//
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Slim\Views\PhpRenderer;

require_once '../vendor/autoload.php';
require_once '../config.php';

require_once SOURCE.'/Helpers.php';

// Controllers
require_once SOURCE.'/controllers/AppController.php';
require_once SOURCE.'/controllers/Bidder.php';

// Initalize app
$app = new \Slim\App(['settings' => $config]);

$container = $app->getContainer();

$container['view'] = new PhpRenderer('./');
$container['db'] = function ($c) {};

/**
 * Default route renders app.php homepage
 * @return view PhpRenderer
 */
$app->get('/', function (Request $request, Response $response, array $args) use($app) {

	return $this->view->render(
		$response,
		'app.php',
		[
			"router" => $this->router,
			"baseUrl" => $request->getUri()->getBaseUrl()
		]
	);
});

//
// REST Api routes
//
// Options:
// -- \ClassName::class, using __invoke method.
// -- \ClassName::class.':method', route HTTP request method to specific class method
// For more details see:
// http://www.slimframework.com/docs/v3/objects/router.html
//

$app->any('/bid', \Controllers\Bidder::class)->setName('bidder');

//
// Remove trailing slash from urls
//
$app->add(function (Request $request, Response $response, callable $next) {
  $uri = $request->getUri();
  $path = $uri->getPath();
  if ($path != '/' && substr($path, -1) == '/') {
    // recursively remove slashes when its more than 1 slash
    while(substr($path, -1) == '/') {
      $path = substr($path, 0, -1);
    }

    // permanently redirect paths with a trailing slash
    // to their non-trailing counterpart
    $uri = $uri->withPath($path);

    if($request->getMethod() == 'GET') {
      return $response->withRedirect((string)$uri, 301);
    }

    return $next($request->withUri($uri), $response);
  }

  return $next($request, $response);
});

$app->run();

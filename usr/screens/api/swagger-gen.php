<?php
require_once __ROOT__ . '/vendor/autoload.php';

// Cargar las clases ANTES del scan para que ReflectionAnalyser las encuentre
require_once __ROOT__ . '/usr/screens/api/SwaggerDocs.php';

$generator = new \OpenApi\Generator();
$generator->setAnalyser(
    new \OpenApi\Analysers\ReflectionAnalyser([
        new \OpenApi\Analysers\AttributeAnnotationFactory(),
    ])
);

$finder = new \OpenApi\SourceFinder(
    __ROOT__ . '/usr/screens/api/SwaggerDocs.php'
);

$openapi = $generator->generate($finder);

header('Content-Type: application/json');
echo $openapi->toJson();

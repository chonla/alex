<?php

require 'vendor/autoload.php';

$r = new Alex\Router();

$r->cors([
    'Access-Control-Allow-Origin' => '*',
]);

$r->get('/', 'hello');
$r->get('/aa', 'hello 2');
$r->get('/bb', 'hello 3');
$r->get('/bb/:id', function($id) {
    return new Alex\JsonResponse(200, [
        'message' => 'It works! ID is '. print_r($id, true)
    ]);
});
$r->get('/cc/test', function() {
    return new Alex\PassThroughResponse('http://www.google.com');
});
$r->post('/cc/test', function() {
    return new Alex\JsonResponse(201, (new Alex\JsonRequest())->toJson());
});
$r->put('/cc/test', function() {
    return new Alex\JsonResponse(200, (new Alex\JsonRequest())->toJson());
});
$r->delete('/cc/test', function() {
    return new Alex\JsonResponse(200, [
        'message' => 'It works! Item has gone'
    ]);
});
$r->options('/cc/test', function() {
    return new Alex\JsonResponse(200);
});

$r->go();
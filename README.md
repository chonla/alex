# Alex

A pico-sized PHP framework.

## Feature

* HTTP Router with named routes.
* PassThrough Response - Non-cached proxy for GET requests.
* Json Response - Return JSON response.

## Example

```
$r = new Alex\Router();
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

$r->go();
```

## Tests

Sorry, no tests so far.

## License

MIT: http://chonla.mit-license.org/
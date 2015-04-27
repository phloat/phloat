phloat
======

This library lets you abstract your application flow. Your application flow basically gets abstracted to events
and actions which react to the dispatched events. Phloat handles this application flow by dispatching events and call
the actions attached to the event.

So with phloat you're able not to just decouple components but also to decouple the flow of your application. phloat
is based on the event driven programming paradigm.

Example
-------
```php
$flow = new Flow();

$flow
	->addAction('comment_startup', new ClosureAction(function(StartUpEvent $event) {
		echo 'Starting up flow' , PHP_EOL;
	}))
	->addAction('comment_shutdown', new ClosureAction(function(ShutdownEvent $event) {
		echo 'Shutting down flow' , PHP_EOL;
	}))
	->addAction('php_error', new ClosureAction(function(PHPErrorOccurredEvent $event) use($flow) {
		echo '<pre>' , $event->getMessage() , ' in file ' , $event->getFile() , ' on line ' , $event->getLine(), '<pre>';
		
		$flow->stop();
	}))
;

$flow->start();
``
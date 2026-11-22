<?php

require_once __DIR__ . '/app/bootstrap.php';

(new App\Core\Router())->dispatch();

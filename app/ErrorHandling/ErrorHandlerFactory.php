<?php

namespace CultuurNet\ProjectAanvraag\ErrorHandling;

use CultuurNet\ProjectAanvraag\ErrorHandler\JsonErrorHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class ErrorHandlerFactory
{
    public static function forWeb(bool $isDebugEnvironment): Run
    {
        $whoops = new Run();
        self::prependWebHandler($whoops, $isDebugEnvironment);
        return $whoops;
    }

    public static function forCli()
    {
        $whoops = new Run();
        $whoops->prependHandler(new PlainTextHandler());
        return $whoops;
    }

    private static function prependWebHandler(Run $whoops, bool $isDebugEnvironment): void
    {
        if ($isDebugEnvironment === true) {
            $whoops->prependHandler(new PrettyPageHandler());
            return;
        }

        $whoops->prependHandler(new JsonErrorHandler());
    }
}

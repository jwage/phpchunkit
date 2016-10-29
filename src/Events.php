<?php

declare(strict_types = 1);

namespace PHPChunkit;

final class Events
{
    const SANDBOX_PREPARE = 'sandbox.prepare';
    const SANDBOX_CLEANUP = 'sandbox.cleanup';
    const DATABASES_CREATE = 'databases.create';
}

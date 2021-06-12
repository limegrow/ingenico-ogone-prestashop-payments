<?php

declare(strict_types=1);

namespace Ingenico\Payment\Install;

use Db;

class InstallerFactory
{
    public static function create(): Installer
    {
        return new Installer(Db::getInstance());
    }
}

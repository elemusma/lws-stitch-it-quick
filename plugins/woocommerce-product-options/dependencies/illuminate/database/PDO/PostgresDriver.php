<?php

namespace Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Database\PDO;

use Barn2\Plugin\WC_Product_Options\Dependencies\Doctrine\DBAL\Driver\AbstractPostgreSQLDriver;
use Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Database\PDO\Concerns\ConnectsToDatabase;
/** @internal */
class PostgresDriver extends AbstractPostgreSQLDriver
{
    use ConnectsToDatabase;
}

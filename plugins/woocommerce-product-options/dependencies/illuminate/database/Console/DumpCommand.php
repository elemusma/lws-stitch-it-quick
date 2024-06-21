<?php

namespace Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Database\Console;

use Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Console\Command;
use Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Contracts\Events\Dispatcher;
use Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Database\Connection;
use Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Database\ConnectionResolverInterface;
use Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Database\Events\SchemaDumped;
use Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Filesystem\Filesystem;
use Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Support\Facades\Config;
/** @internal */
class DumpCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'schema:dump
                {--database= : The database connection to use}
                {--path= : The path where the schema dump file should be stored}
                {--prune : Delete all existing migration files}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dump the given database schema';
    /**
     * Execute the console command.
     *
     * @param  \Illuminate\Database\ConnectionResolverInterface  $connections
     * @param  \Illuminate\Contracts\Events\Dispatcher  $dispatcher
     * @return int
     */
    public function handle(ConnectionResolverInterface $connections, Dispatcher $dispatcher)
    {
        $connection = $connections->connection($database = $this->input->getOption('database'));
        $this->schemaState($connection)->dump($connection, $path = $this->path($connection));
        $dispatcher->dispatch(new SchemaDumped($connection, $path));
        $this->info('Database schema dumped successfully.');
        if ($this->option('prune')) {
            (new Filesystem())->deleteDirectory(database_path('migrations'), $preserve = \false);
            $this->info('Migrations pruned successfully.');
        }
    }
    /**
     * Create a schema state instance for the given connection.
     *
     * @param  \Illuminate\Database\Connection  $connection
     * @return mixed
     */
    protected function schemaState(Connection $connection)
    {
        return $connection->getSchemaState()->withMigrationTable($connection->getTablePrefix() . Config::get('database.migrations', 'migrations'))->handleOutputUsing(function ($type, $buffer) {
            $this->output->write($buffer);
        });
    }
    /**
     * Get the path that the dump should be written to.
     *
     * @param  \Illuminate\Database\Connection  $connection
     */
    protected function path(Connection $connection)
    {
        return \Barn2\Plugin\WC_Product_Options\Helpers::tap($this->option('path') ?: database_path('schema/' . $connection->getName() . '-schema.dump'), function ($path) {
            (new Filesystem())->ensureDirectoryExists(\dirname($path));
        });
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CreateService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service class';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');
        $servicePath = app_path('Services\/' . $name . '.php');

        if (File::exists($servicePath)) {
            $this->error("Service {$name} already exists.");
            return;
        }

        $template = <<<CLASS
<?php

namespace App\Services;

class {$name}
{
    //
}
CLASS;

        File::put($servicePath, $template);
        $this->info("Service {$name} created successfully.");
    }
}

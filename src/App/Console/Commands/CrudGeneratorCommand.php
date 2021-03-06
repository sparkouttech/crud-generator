<?php

namespace Chillout\CrudGenerator\App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use File;

class CrudGeneratorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:generate {name : Class (singular) for example User}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create crud by command line';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');

        $this->controller($name);
        $this->model($name);
        $this->request($name);
        $this->migration($name);
    
        File::append(base_path('routes/api.php'), 'Route::resource(\'' . Str::plural(strtolower($name)) . "', '{$name}Controller');");
    }

    protected function getStub($type)
    {
        return file_get_contents(__DIR__ ."/../../../resources/stubs/$type.stub");
    }


    protected function model($name)
    {
        $modelTemplate = str_replace(
            ['{{modelName}}'],
            [$name],
            $this->getStub('Model')
        );

        file_put_contents(app_path("/Models/{$name}.php"), $modelTemplate);
    }

    protected function request($name)
    {
        $requestTemplate = str_replace(
            ['{{modelName}}'],
            [$name],
            $this->getStub('Request')
        );

        if(!file_exists($path = app_path('/Http/Requests')))
            mkdir($path, 0777, true);

        file_put_contents(app_path("/Http/Requests/{$name}Request.php"), $requestTemplate);
    }

    protected function controller($name)
    {
        $controllerTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}'
            ],
            [
                $name,
                strtolower(Str::plural($name)),
                strtolower($name)
            ],
            $this->getStub('Controller')
        );

        file_put_contents(app_path("/Http/Controllers/{$name}Controller.php"), $controllerTemplate);
    }

    protected function migration($name)
    {
        $migrationTemplate = str_replace(
            ['{{modelName}}','{{modelNamePluralLowerCase}}',],
            [Str::plural($name),strtolower(Str::plural($name))],
            $this->getStub('Migration')
        );
        $name = strtolower(Str::plural($name));
        file_put_contents(base_path("database/migrations/".date('Y_m_d_His')."_create_{$name}_table.php"), $migrationTemplate);
    }
}

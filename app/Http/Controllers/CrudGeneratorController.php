<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class CrudGeneratorController extends Controller
{
    public function generate(Request $request)
    {
        $json = $request->input('json');

        $spec = json_decode($json, true);
        if (!$spec || !isset($spec['columns'])) {
            return back()->withErrors('Invalid JSON or missing columns');
        }

        $modelName = Str::studly($request->input('model_name'));
        $modelVar = Str::camel($modelName);
        $tableName = Str::snake(Str::plural($modelName));
        $controllerName = $modelName . 'Controller';

        $outputDir = base_path("generate/crud/{$modelName}");
        File::ensureDirectoryExists($outputDir);

        // Prepare columns info for stubs
        $fields = array_column($spec['columns'], 'name');
        $validations = [];
        foreach ($spec['columns'] as $col) {
            $validations[] = [
                'field' => $col['name'],
                'rule' => $col['validation'] ?? 'required',
            ];
        }

        // --- Generate Migration ---
        $migrationStubPath = resource_path('stubs/crud/migration.stub');
        $migrationStub = File::get($migrationStubPath);

        $migrationContent = str_replace(
            ['{{modelNamePlural}}', '{{tableName}}', '{{fields}}'],
            [Str::plural($modelName), $tableName, $this->generateMigrationFields($spec['columns'])],
            $migrationStub
        );

        $migrationFileName = "create_{$tableName}_table.php";
        File::put("{$outputDir}/{$migrationFileName}", $migrationContent);

        // --- Generate Model ---
        $modelStubPath = resource_path('stubs/crud/model.stub');
        $modelStub = File::get($modelStubPath);
        $modelContent = str_replace(
            ['{{modelName}}', '{{tableName}}'],
            [$modelName, $tableName],
            $modelStub
        );
        File::put("{$outputDir}/{$modelName}.php", $modelContent);

        // --- Generate Controller ---
        $controllerStubPath = resource_path('stubs/crud/controller.stub');
        $controllerStub = File::get($controllerStubPath);

        $controllerContent = str_replace(
            [
                '{{modelName}}',
                '{{modelVar}}',
                '{{tableName}}',
                '{{validations}}',
            ],
            [
                $modelName,
                $modelVar,
                $tableName,
                $this->generateValidationRules($validations),
            ],
            $controllerStub
        );

        File::put("{$outputDir}/{$controllerName}.php", $controllerContent);

        // --- Generate Views ---
        $views = ['index', 'create', 'edit', 'show'];
        foreach ($views as $view) {
            $viewStubPath = resource_path("stubs/crud/{$view}.blade.stub");
            $viewStub = File::get($viewStubPath);

            $viewContent = str_replace(
                [
                    '{{modelName}}',
                    '{{modelNamePlural}}',
                    '{{modelVar}}',
                    '{{fields}}',
                    '{{fieldsLoop}}',
                ],
                [
                    $modelName,
                    Str::plural($modelName),
                    $modelVar,
                    $this->generateFieldsForViews($fields, $modelVar),
                    $this->generateFieldsLoopForViews($fields, $modelVar),
                ],
                $viewStub
            );

            File::put("{$outputDir}/{$view}.blade.php", $viewContent);
        }

        return redirect()->back()->with('success', 'CRUD generated successfully!');
    }

    /**
     * Helper to generate migration fields.
     */
    private function generateMigrationFields(array $columns): string
    {
        $lines = [];
        foreach ($columns as $col) {
            $type = $col['type'] ?? 'string';
            $nullable = !empty($col['nullable']) ? '->nullable()' : '';
            $lines[] = "            \$table->{$type}('{$col['name']}'){$nullable};";
        }
        return implode("\n", $lines);
    }

    /**
     * Helper to generate validation rules string.
     */
    private function generateValidationRules(array $validations): string
    {
        $lines = [];
        foreach ($validations as $val) {
            $lines[] = "            '{$val['field']}' => '{$val['rule']}',";
        }
        return implode("\n", $lines);
    }

    /**
     * Helper to generate form fields HTML for views.
     */
    private function generateFieldsForViews(array $fields, string $modelVar): string
    {
        $html = '';
        foreach ($fields as $field) {
            $html .= <<<HTML
        <div class="mb-3">
            <label for="{$field}" class="form-label">{$field}</label>
            <input type="text" class="form-control" id="{$field}" name="{$field}" value="{{ old('{$field}', \${$modelVar}->{$field} ?? '') }}">
        </div>

HTML;
        }
        return $html;
    }

    /**
     * Helper to generate table headers for index view.
     */
    private function generateFieldsLoopForViews(array $fields, string $modelVar): string
    {
        $html = '';
        foreach ($fields as $field) {
            $html .= "                <th>{$field}</th>\n";
        }
        return $html;
    }
}

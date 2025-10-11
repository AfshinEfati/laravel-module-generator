<?php

namespace Efati\ModuleGenerator\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;

class ModuleGeneratorController extends Controller
{
    public function index()
    {
        return view('module-generator::index');
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|alpha_dash',
            'options' => 'array',
            'fields' => 'nullable|string',
        ]);

        $name = $validated['name'];
        $options = $validated['options'] ?? [];
        $fields = $validated['fields'] ?? null;

        $command = 'make:module';
        $parameters = [
            'name' => $name,
        ];

        foreach ($options as $option => $value) {
            if ($value) {
                $parameters['--' . $option] = true;
            }
        }

        if ($fields) {
            $parameters['--fields'] = $fields;
        }

        try {
            Artisan::call($command, $parameters);
            $output = Artisan::output();
            return response()->json(['success' => true, 'output' => $output]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
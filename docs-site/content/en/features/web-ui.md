# Web UI

The Laravel Module Generator now includes a web-based user interface (UI) that allows you to generate modules interactively. This UI is inspired by the Spatie Laravel Permission UI and provides a simple and intuitive way to generate modules without using the command line.

## Installation

To use the web UI, you first need to publish the necessary assets. You can do this by running the following command:

```bash
php artisan vendor:publish --provider="Efati\\ModuleGenerator\\ModuleGeneratorServiceProvider" --tag=module-generator-views
```

This command will publish the UI's views and assets to your application's `resources/views/vendor/module-generator` directory.

## Usage

Once you have published the assets, you can access the web UI by visiting the `/module-generator` route in your application.

The UI provides a form where you can enter the module's name and select the components you want to generate. You can also provide a schema for the module's model, just like you would with the `--fields` option in the command-line interface.

After you have configured the module, you can click the "Generate" button to create the module. The UI will then display the generated files and their paths.

## Customization

You can customize the web UI by editing the published views in the `resources/views/vendor/module-generator` directory. This allows you to change the UI's appearance and behavior to match your application's design.
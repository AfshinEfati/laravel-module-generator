<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Module Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/vue@3"></script>
</head>
<body class="bg-gray-100">
    <div id="app" class="container mx-auto p-8">
        <h1 class="text-2xl font-bold mb-4">Laravel Module Generator</h1>
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <form @submit.prevent="generateModule">
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 font-bold mb-2">Module Name</label>
                    <input type="text" id="name" v-model="name" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-bold mb-2">Options</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex items-center">
                            <input type="checkbox" v-model="options.api" class="form-checkbox">
                            <span class="ml-2">API Controller</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" v-model="options.requests" class="form-checkbox">
                            <span class="ml-2">Form Requests</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" v-model="options.tests" class="form-checkbox">
                            <span class="ml-2">Feature Tests</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" v-model="options.swagger" class="form-checkbox">
                            <span class="ml-2">Swagger</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" v-model="options.actions" class="form-checkbox">
                            <span class="ml-2">Actions</span>
                        </label>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="fields" class="block text-gray-700 font-bold mb-2">Fields</label>
                    <textarea id="fields" v-model="fields" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" rows="4"></textarea>
                    <p class="text-gray-600 text-xs italic">e.g., name:string:unique, price:decimal(10,2), is_active:boolean</p>
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Generate Module
                    </button>
                </div>
            </form>
            <div v-if="output" class="mt-4 p-4 bg-gray-200 rounded">
                <h3 class="font-bold">Output:</h3>
                <pre class="whitespace-pre-wrap">{{ output }}</pre>
            </div>
            <div v-if="error" class="mt-4 p-4 bg-red-200 text-red-800 rounded">
                <h3 class="font-bold">Error:</h3>
                <p>{{ error }}</p>
            </div>
        </div>
    </div>

    <script>
        const { createApp } = Vue

        createApp({
            data() {
                return {
                    name: '',
                    options: {
                        api: true,
                        requests: false,
                        tests: false,
                        swagger: false,
                        actions: false,
                    },
                    fields: '',
                    output: '',
                    error: '',
                }
            },
            methods: {
                generateModule() {
                    this.output = '';
                    this.error = '';

                    fetch('{{ route("module-generator.generate") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            name: this.name,
                            options: this.options,
                            fields: this.fields,
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.output = data.output;
                        } else {
                            this.error = data.message;
                        }
                    })
                    .catch(error => {
                        this.error = 'An unexpected error occurred.';
                    });
                }
            }
        }).mount('#app')
    </script>
</body>
</html>
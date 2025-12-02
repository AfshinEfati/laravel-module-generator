# 📚 Documentation Index

Complete guide to Laravel Module Generator with built-in Swagger support.

---

## 🚀 Getting Started

**New to the package?** Start here:

### 1. [Quick Start](SWAGGER_QUICKSTART.md)
   - ⏱️ 5-minute setup
   - Basic module generation
   - View first documentation
   - **Read this first!**

### 2. [README](README.md)
   - Package overview
   - Key features
   - Installation steps
   - Basic examples

---

## 📖 Core Documentation

### 3. [PHPDoc Generation](SWAGGER_PHPDOC_GENERATION.md)
   - How to generate `@OA\` annotations
   - Module-based documentation
   - Project-wide documentation
   - File structure and format

### 4. [Command Reference](COMMAND_REFERENCE.md)
   - Complete command signatures
   - All available options
   - Usage examples
   - Workflow examples

### 5. [Configuration Guide](SWAGGER_CONFIG.md)
   - All configuration options
   - Environment variables
   - Customize themes and colors
   - Advanced settings

---

## 🔧 Integration & Advanced

### 6. [No Dependencies Approach](SWAGGER_NO_DEPENDENCIES.md)
   - Zero external packages
   - Built-in Swagger UI
   - Architecture overview
   - Troubleshooting

### 7. [Integration Guide](INTEGRATION_GUIDE.md)
   - Optional external packages
   - Swagger-PHP integration
   - L5-Swagger integration
   - Comparison table

### 8. [Customization](SWAGGER_UI_CUSTOMIZATION.md)
   - Customize UI appearance
   - Theme configuration
   - CSS variables
   - Layout options

### 9. [UI Themes](SWAGGER_UI_THEMES.md)
   - Available themes (vanilla, tailwind, dark)
   - Theme switching
   - Custom theme creation

---

## 📋 Feature Documentation

### 10. [Features Overview](FEATURES.md)
   - Module generation features
   - Schema inference
   - Validation handling
   - Test generation

### 11. [Examples](EXAMPLES.md)
   - Practical examples
   - Complete workflows
   - Common patterns
   - Best practices

### 12. [API Reference](API_REFERENCE.md)
   - Generated classes
   - Available methods
   - Type hints
   - Relationships

---

## 🔍 Quick Reference

### Most Common Commands

```bash
# Generate module with swagger
php artisan make:module Product --swagger

# Generate docs for all routes
php artisan make:swagger --force

# Configure UI
php artisan swagger:config

# Initialize UI
php artisan swagger:init

# View documentation
php artisan swagger:ui
```

---

## 📖 By Use Case

### "I want to generate a complete API module"
1. Read: [Quick Start](SWAGGER_QUICKSTART.md)
2. Run: `php artisan make:module Product -a --swagger --tests`
3. Reference: [Command Reference](COMMAND_REFERENCE.md)

### "I want to customize the Swagger UI"
1. Read: [Configuration Guide](SWAGGER_CONFIG.md)
2. Read: [Customization](SWAGGER_UI_CUSTOMIZATION.md)
3. Read: [UI Themes](SWAGGER_UI_THEMES.md)
4. Run: `php artisan swagger:config`

### "I need to generate only documentation"
1. Read: [PHPDoc Generation](SWAGGER_PHPDOC_GENERATION.md)
2. Run: `php artisan make:swagger --force`
3. Edit: `app/Docs/ProductDoc.php`

### "I want to add external swagger packages"
1. Read: [Integration Guide](INTEGRATION_GUIDE.md)
2. Compare: No Dependencies vs Swagger-PHP vs L5-Swagger
3. Install: `composer require zircote/swagger-php`
4. Integrate: Follow workflow steps

### "I want to understand the architecture"
1. Read: [No Dependencies Approach](SWAGGER_NO_DEPENDENCIES.md)
2. Read: [Features Overview](FEATURES.md)
3. Check: Generated files in `app/Docs/`

### "I have a problem or error"
1. Check: [Troubleshooting](#troubleshooting)
2. Search: [Command Reference](COMMAND_REFERENCE.md)
3. Run: `php artisan list | grep swagger`

---

## 🏗️ Architecture Overview

```
Laravel Module Generator
├── Module Generation
│   ├── Repository
│   ├── Service
│   ├── DTO
│   ├── Controller
│   ├── Resource
│   ├── Form Request
│   ├── Tests
│   └── Provider
│
├── Swagger Documentation
│   ├── PHPDoc Generator (app/Docs/*.php)
│   ├── JSON Spec Generator (storage/app/swagger.json)
│   ├── UI Server (public/docs/index.html)
│   └── Configuration Manager (config/module-generator.php)
│
└── Optional Integration
    ├── Swagger-PHP (zircote/swagger-php)
    └── L5-Swagger (darkaonline/l5-swagger)
```

---

## 🎯 Feature Checklist

### Module Generation
- ✅ API/Web controllers
- ✅ Repositories with contracts
- ✅ Services with contracts
- ✅ DTOs with type hints
- ✅ Form requests with validation
- ✅ API resources
- ✅ Feature tests
- ✅ Service providers
- ✅ Action classes (optional)

### Swagger Documentation
- ✅ PHPDoc annotations (`@OA\` tags)
- ✅ Route scanning
- ✅ Validation rule conversion
- ✅ OpenAPI 3.0 spec generation
- ✅ JSON specification output
- ✅ Interactive UI
- ✅ Theme customization
- ✅ Dark mode support

### Schema Inference
- ✅ From migrations
- ✅ From inline fields
- ✅ From models
- ✅ Validation rules mapping

### Configuration
- ✅ Environment variables
- ✅ Interactive setup
- ✅ Theme selection
- ✅ Color customization
- ✅ Font selection
- ✅ Dark mode toggle

---

## 📝 File Organization

```
Laravel-Scaffolder/
├── README.md                           ← Start here
├── SWAGGER_QUICKSTART.md               ← 5-minute setup
├── SWAGGER_PHPDOC_GENERATION.md        ← How to generate docs
├── COMMAND_REFERENCE.md                ← All commands
├── SWAGGER_CONFIG.md                   ← Configuration
├── SWAGGER_NO_DEPENDENCIES.md          ← Architecture
├── INTEGRATION_GUIDE.md                ← External packages
├── SWAGGER_UI_CUSTOMIZATION.md         ← UI customization
├── SWAGGER_UI_THEMES.md                ← Available themes
├── FEATURES.md                         ← Feature overview
├── EXAMPLES.md                         ← Practical examples
├── API_REFERENCE.md                    ← API details
├── CONTRIBUTING.md
├── CHANGELOG.md
├── CODE_OF_CONDUCT.md
└── src/
    ├── Commands/
    │   ├── MakeModuleCommand.php
    │   ├── GenerateSwaggerCommand.php
    │   ├── SwaggerConfigCommand.php
    │   ├── SwaggerInitCommand.php
    │   ├── SwaggerUICommand.php
    │   └── SwaggerGenerateCommand.php
    ├── Generators/
    │   ├── SwaggerDocGenerator.php
    │   └── ... (other generators)
    ├── Support/
    │   └── SwaggerConfigManager.php
    └── config/
        └── module-generator.php
```

---

## ⚡ Troubleshooting

### "Command not found"
```bash
composer dump-autoload
php artisan package:discover
```

### "Files not generating"
```bash
php artisan make:swagger --force
ls -la app/Docs/
```

### "UI not displaying"
```bash
php artisan swagger:init
php artisan swagger:ui
```

### "Configuration not working"
```bash
php artisan swagger:config --show
php artisan config:cache
```

👉 Full troubleshooting in [No Dependencies Approach](SWAGGER_NO_DEPENDENCIES.md#troubleshooting)

---

## 🤝 Contributing

Contributions are welcome! See [CONTRIBUTING.md](CONTRIBUTING.md)

---

## 📄 License

MIT License. See [LICENSE](LICENSE) file.

---

## 🔗 Links

- **GitHub:** [Laravel-Scaffolder](https://github.com/AfshinEfati/Laravel-Scaffolder)
- **Docs Site:** [afshinefati.github.io](https://afshinefati.github.io/Laravel-Scaffolder/)
- **Packagist:** [efati/Laravel-Scaffolder](https://packagist.org/packages/efati/Laravel-Scaffolder)

---

## 🎓 Learning Path

**Beginner:**
1. [Quick Start](SWAGGER_QUICKSTART.md) - 5 minutes
2. [README](README.md) - 10 minutes
3. [PHPDoc Generation](SWAGGER_PHPDOC_GENERATION.md) - 15 minutes

**Intermediate:**
1. [Command Reference](COMMAND_REFERENCE.md) - 20 minutes
2. [Configuration Guide](SWAGGER_CONFIG.md) - 15 minutes
3. [Examples](EXAMPLES.md) - 15 minutes

**Advanced:**
1. [No Dependencies Approach](SWAGGER_NO_DEPENDENCIES.md) - 20 minutes
2. [Integration Guide](INTEGRATION_GUIDE.md) - 20 minutes
3. [Customization](SWAGGER_UI_CUSTOMIZATION.md) - 30 minutes

**Total Learning Time:** ~2-3 hours

---

## 📞 Support

- 💬 GitHub Issues: Report bugs and request features
- 📧 Email: Check CONTRIBUTING.md for contact info
- 📚 Docs: You're already here!

---

**Last Updated:** 2024
**Version:** Check [README.md](README.md) for latest version

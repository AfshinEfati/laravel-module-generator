# Laravel Module Generator

A clean and extensible Laravel module generator designed for API-based projects.

Generates Repository, Service, Interfaces, DTOs, Tests, and Service Providers based on best practices.

---

## 📦 Installation

```bash
composer require efati/laravel-module-generator --dev
```

(Optional) To publish base classes:

```bash
php artisan vendor:publish --tag=module-base
```

---

## 🚀 Usage

```bash
php artisan make:module Company
```

This will generate:

- `Repositories/Eloquent/CompanyRepository.php`
- `Repositories/Contracts/CompanyRepositoryInterface.php`
- `Services/CompanyService.php`
- `Services/Contracts/CompanyServiceInterface.php`
- `DTOs/CompanyDTO.php` (auto-filled from model)
- `Providers/CompanyServiceProvider.php`
- `Tests/Feature/CompanyTest.php`

You must have a model named `Company` before running the command.

---

## 📂 Base Classes

Use `vendor:publish` to copy these reusable classes:

- `BaseRepository`
- `BaseRepositoryInterface`
- `BaseService`
- `BaseServiceInterface`

These provide common logic for reuse across all modules.

---

## 💡 Example

To generate a module for `Flight`:

```bash
php artisan make:module Flight
```

---

## 🧪 Requirements

- Laravel 11+
- PHP 8.1+
- Model must exist and define `$fillable` for DTO generation

---

## 🧑‍💻 Author

Made with ❤️ by [Afshin](https://github.com/AfshinEfati)

---

## 🇮🇷 راهنمای فارسی

پکیج `Laravel Module Generator` برای ساخت سریع و تمیز ماژول‌های لاراولی طراحی شده که کاملاً سازگار با پروژه‌های API محور هست.

---

## ⚙️ نصب پکیج

```bash
composer require efati/laravel-module-generator --dev
```

برای انتشار فایل‌های پایه:

```bash
php artisan vendor:publish --tag=module-base
```

---

## 🛠 استفاده

مثال:

```bash
php artisan make:module Company
```

موارد زیر ساخته خواهند شد:

- `CompanyRepository` و `Interface`
- `CompanyService` و `Interface`
- `CompanyDTO` با فیلدهای خودکار از مدل
- `CompanyServiceProvider`
- `CompanyTest`

📌 مدل `Company` باید از قبل ساخته شده باشه و `fillable` داشته باشه.

---

## 📦 کلاس‌های پایه (Base)

با اجرای:

```bash
php artisan vendor:publish --tag=module-base
```

فایل‌های پایه‌ای زیر ایجاد می‌شن:

- `BaseRepository`  
- `BaseRepositoryInterface`  
- `BaseService`  
- `BaseServiceInterface`  

برای جلوگیری از تکرار و رعایت اصول SOLID در لایه‌ها.

---

## 🧠 نکات نهایی

- فقط برای پروژه‌های API محور
- بدون وابستگی به UI
- مناسب برای پروژه‌های ماژولار و تجاری

---

## ✨ نویسنده

ساخته شده با ❤️ توسط [افشین افتی](https://github.com/efati)

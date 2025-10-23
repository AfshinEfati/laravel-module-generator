# راهنمای مشارکت / Contribution Guidelines

## به زبان فارسی

از اینکه مایل به مشارکت در پروژه Laravel Module Generator هستید، متشکریم! لطفاً قبل از ارسال درخواست‌های Pull، این راهنما را با دقت مطالعه کنید.

### نحوه مشارکت

1. ابتدا مخزن را Fork کنید
2. شاخه‌ای برای ویژگی جدید خود ایجاد کنید (`git checkout -b feature/AmazingFeature`)
3. تغییرات خود را کامیت کنید (`git commit -m 'Add some AmazingFeature'`)
4. تغییرات را به شاخه خود Push کنید (`git push origin feature/AmazingFeature`)
5. یک Pull Request باز کنید

### استانداردهای کدنویسی

- از استانداردهای کدنویسی PSR-12 پیروی کنید
- توابع و متدها باید مستندات PHPDoc داشته باشند
- کدهای جدید باید شامل تست‌های واحد باشند
- مطمئن شوید تمام تست‌ها با موفقیت اجرا می‌شوند

### گزارش باگ و درخواست ویژگی

قبل از ثبت یک issue جدید:
- مطمئن شوید که issue تکراری نیست
- در مورد باگ‌ها، مراحل بازتولید باگ را به دقت شرح دهید
- برای درخواست ویژگی‌های جدید، توضیح دهید که چرا این ویژگی مفید است

### محیط توسعه

- PHP 8.0 یا بالاتر
- کامپوزر (آخرین نسخه پایدار)
- PHPUnit برای اجرای تست‌ها

### اجرای تست‌ها

```bash
composer test
```
### مجوز

با مشارکت در این پروژه، شما موافقت می‌کنید که مشارکت‌های شما تحت مجوز MIT مجوزدهی شود.

---

## In English

Thank you for your interest in contributing to Laravel Module Generator! Please take a moment to review this document before submitting a pull request.

### How to Contribute

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Coding Standards

- Follow PSR-12 coding standards
- All functions and methods must have PHPDoc blocks
- New code must be covered by unit tests
- Ensure all tests pass successfully

### Reporting Bugs & Feature Requests

Before opening a new issue:
- Check if the issue already exists
- For bugs, include steps to reproduce
- For feature requests, explain why this feature would be useful

### Development Environment

- PHP 8.0 or higher
- Composer (latest stable version)
- PHPUnit for running tests

### Running Tests

```bash
composer test
```

### License

By contributing, you agree that your contributions will be licensed under the MIT License.

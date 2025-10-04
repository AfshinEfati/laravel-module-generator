---
title: ژنراتور ماژول لاراول
hide:
  - navigation
---

<div dir="rtl" markdown="1">

[🇬🇧 English](../en/index.md){ .language-switcher }

<div class="hero">
  <div class="hero__content">
    <span class="hero__eyebrow">Laravel Module Generator</span>
    <h1 class="hero__title">ماژول‌های لاراول را در چند دقیقه بسازید</h1>
    <p class="hero__lead">با یک دستور آرتیزان کنترلر، DTO، سرویس، ریپازیتوری، ریسورس و تست ایجاد کنید. استاب‌ها را شخصی‌سازی کنید و مطمئن باشید خروجی هر ماژول قابل پیش‌بینی و هماهنگ است.</p>
    <div class="hero__actions">
      <a class="md-button md-button--primary" href="installation/">راهنمای نصب</a>
      <a class="md-button md-button--secondary" href="quickstart/">اولین ماژول را بسازید</a>
      <a class="md-button" href="https://github.com/AfshinEfati/laravel-module-generator" target="_blank" rel="noopener">مشاهده در گیت‌هاب</a>

    </div>
  </div>
</div>

## چرا انتخابش کنیم؟

- فیلدها را یک بار تعریف کنید و ژنراتور DTO، درخواست‌های اعتبارسنجی، ریسورس‌ها، فکتوری‌ها و تست‌ها را هماهنگ با همان نام‌گذاری می‌سازد.
- کنترلرها با پاسخ‌های استاندارد، صفحه‌بندی و اتصال به ریسورس آماده می‌شوند تا نیاز به پرداخت اضافی نباشد.
- با انتشار استاب‌ها می‌توانید نام‌فضا، لاگینگ و متون بومی‌سازی را مطابق استاندارد تیم تنظیم کنید.

## شروع سریع

<div class="landing-grid">
  <div class="landing-card" markdown="1">
    <h3>نصب بسته</h3>
    ```bash
    composer require efati/laravel-module-generator
    ```
    <p>پس از نصب، Service Provider به‌صورت خودکار دستور <code>make:module</code> را رجیستر می‌کند.</p>
  </div>
  <div class="landing-card" markdown="1">
    <h3>ساخت یک ماژول</h3>
    ```bash
    php artisan make:module Product \
      --api --requests --tests \
      --fields="name:string, price:decimal(10,2), is_active:boolean"
    ```
    <p>تمام کنترلرها، ریسورس‌ها، DTOها، ریپازیتوری‌ها، سرویس‌ها و تست‌ها در یک مرحله تولید می‌شوند.</p>
  </div>
</div>

## چه چیزهایی آماده می‌شود؟

- Service Providerها ریپازیتوری، سرویس، پالیسی و آبزرورهای شما را ثبت می‌کنند.
- استاب‌های قابل‌تنظیم ساختار پوشه‌ها و کلاس‌های پایه را در اختیار شما می‌گذارند.
- تست‌های فیچر و یونیت به همراه فکتوری تولید می‌شوند تا سریع رفتار را بررسی کنید.

## بیشتر یاد بگیرید

- با [چک‌لیست نصب](installation.md) تنظیمات و فایل‌ها را منتشر کنید.
- از [راهنمای شروع سریع](quickstart.md) برای تعریف اسکیم یا استفاده از مایگریشن موجود بهره ببرید.
- وقتی نیاز به کنترل بیشتر داشتید، به [الگوهای استفاده](usage.md)، [راهنماهای پیشرفته](advanced.md) و [مرجع CLI](reference.md) سر بزنید.


</div>

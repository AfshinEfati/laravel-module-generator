---
title: ژنراتور ماژول لاراول
hide:
  - navigation
---

<div dir="rtl">

[🇬🇧 English](/en/){ .language-switcher }

<div class="hero">
  <div class="hero__content">
    <span class="hero__eyebrow">نسخه ۶ ژنراتور ماژول لاراول</span>
    <h1 class="hero__title">ساخت ماژول‌های منسجم لاراول در چند دقیقه</h1>
    <p class="hero__lead">با یک بار تعریف اسکیما، DTO، فرم‌ریکوئست، ریسورس، تست و سرویس‌پرووایدر را تولید کنید تا هر ماژول با ساختار یکنواخت و آمادهٔ انتشار ساخته شود.</p>
    <div class="hero__actions">
      <a class="md-button md-button--primary" href="/fa/quickstart/">شروع سریع از خط فرمان</a>
      <a class="md-button md-button--secondary" href="/fa/installation/">نصب بسته</a>

      <a class="md-button" href="https://github.com/efati/laravel-module-generator" target="_blank" rel="noopener">⭐️ در گیت‌هاب</a>
    </div>
  </div>
</div>

<div class="badge-row">
  <a href="https://packagist.org/packages/efati/laravel-module-generator" target="_blank" rel="noopener">
    <img alt="نسخه در Packagist" src="https://img.shields.io/packagist/v/efati/laravel-module-generator.svg?label=packagist&color=4c51bf">
  </a>
  <a href="https://packagist.org/packages/efati/laravel-module-generator" target="_blank" rel="noopener">
    <img alt="دانلودهای Packagist" src="https://img.shields.io/packagist/dt/efati/laravel-module-generator.svg?color=10b981">
  </a>
  <a href="https://github.com/efati/laravel-module-generator" target="_blank" rel="noopener">
    <img alt="ستاره‌های GitHub" src="https://img.shields.io/github/stars/efati/laravel-module-generator.svg?style=flat&color=0ea5e9">
  </a>
  <a href="https://github.com/efati/laravel-module-generator/actions/workflows/docs.yml" target="_blank" rel="noopener">
    <img alt="وضعیت ساخت مستندات" src="https://img.shields.io/github/actions/workflow/status/efati/laravel-module-generator/docs.yml?branch=main&label=docs">
  </a>
</div>

## چرا تیم‌ها این ابزار را انتخاب می‌کنند

<div class="feature-grid">
  <div class="feature-card">
    <h3>:octicons-rows-16: اسکفولدینگ مبتنی بر اسکیما</h3>
    <p>فقط یک بار فیلدها را به‌صورت درون‌خطی یا از روی مایگریشن تعریف کنید تا DTO، قوانین اعتبارسنجی، ریسورس، کارخانه و تست‌ها بدون تکرار متادیتا ساخته شوند.</p>
  </div>
  <div class="feature-card">
    <h3>:octicons-rocket-16: پیش‌فرض‌های آمادهٔ تولید</h3>
    <p>کنترلرهای تولیدشده همراه با هِلپر پاسخ، پشتیبانی صفحه‌بندی و متن‌های قابل بومی‌سازی هستند تا بدون پرداختن به جزئیات اضافی منتشر شوند.</p>
  </div>
  <div class="feature-card">
    <h3>:octicons-code-square-16: قالب‌های قابل توسعه</h3>
    <p>پس از انتشار قالب‌ها، نام‌فضا، تریت‌ها یا لاگ‌گیری را مطابق با استاندارد داخلی خود شخصی‌سازی کنید و همچنان با به‌روزرسانی‌های بسته سازگار بمانید.</p>
  </div>
  <div class="feature-card">
    <h3>:octicons-circuit-board-16: سرویس‌پرووایدر آگاه از کانتینر</h3>
    <p>سرویس‌پرووایدر ماژول ریپازیتوری‌ها، سرویس‌ها، آبزرورها و پالیسی‌ها را به شکل خودکار ثبت می‌کند تا گراف وابستگی همیشه همگام بماند.</p>
  </div>
</div>

<div class="quickstart">
  <h2>شروع سریع از CLI</h2>
  <p>با توجه به نیاز پروژه یکی از سناریوها را انتخاب کنید و ماژولی کامل شامل تست و API دریافت کنید.</p>

=== "اسکیما درون‌خطی"

    ```bash
    composer require efati/laravel-module-generator
    php artisan make:module Product \
      --api --requests --tests \
      --fields="name:string:unique, price:decimal(10,2), is_active:boolean"
    ```

=== "استفاده از مایگریشن موجود"

    ```bash
    composer require efati/laravel-module-generator
    php artisan vendor:publish --tag=module-generator
    php artisan make:module Product \
      --api --requests --tests \
      --from-migration=database/migrations/2024_05_01_000000_create_products_table.php
    ```

</div>

## گردش کار را یاد بگیرید

- چک‌لیست [نصب](/fa/installation/) را مرور کنید و فایل‌های قابل پیکربندی را منتشر نمایید.
- با [راهنمای شروع سریع](/fa/quickstart/) اولین ماژول کامل خود را بسازید.
- در [راهنمای استفاده](/fa/usage/) دستورهای پرکاربرد، سناریوهای DTO و استراتژی تست را ببینید.
- در [راهنمای پیشرفته](/fa/advanced/) روش شخصی‌سازی قالب‌ها، هوک‌ها و ژنراتورهای اختصاصی را یاد بگیرید.

## ساختار خروجی را بررسی کنید

بخش مرجع توضیح می‌دهد چه فایل‌هایی ساخته می‌شود، سرویس‌پرووایدر چگونه سیم‌کشی می‌شود و هر فلگ چه خروجی‌ای فعال می‌کند.

- [راهنمای مرجع CLI و فایل‌ها](/fa/reference/) را برای مشاهدهٔ سریع گزینه‌ها مطالعه کنید.
- تغییرات را در [تغییرات نسخه‌ها](/fa/changelog/) دنبال کنید.
- با [راهنمای GitHub Pages](/fa/github-pages-setup/) مستندات را به‌صورت خودکار منتشر کنید.


<div class="cta-banner">
  <h2>آمادهٔ ساخت ماژول بعدی هستید؟</h2>
  <p>بسته را نصب کنید، یک بار اسکیما را توضیح دهید و اجازه دهید ژنراتور در چند دقیقه ماژول لاراولی تست‌شده تحویل دهد.</p>
  <a class="md-button md-button--primary" href="/fa/installation/">نصب و پیکربندی</a>

</div>

</div>

---
title: ژنراتور ماژول لاراول
---

[🇬🇧 English](../en/index.md){ .language-switcher }

<section class="hero" markdown="1" dir="rtl">
  <div class="hero__content" markdown="1">
    <p class="hero__eyebrow">ساخت ماژول لاراول در چند دقیقه</p>
    <h1 class="hero__title">ماژول‌های کامل را فقط با یک دستور بسازید</h1>
    <p class="hero__lead">
      با یک دستور آرتیزان، کنترلر، DTO، سرویس، ریپازیتوری، ریسورس، فرم‌ریکوئست، تست و حتی مستندات را بسازید.
      استاب‌ها را هر طور که می‌خواهید تغییر دهید تا خروجی با استاندارد تیم شما هم‌خوان باشد.
    </p>
    <div class="hero__actions">
      [شروع کنید](installation.md){ .md-button .md-button--primary }
      [مشاهده در گیت‌هاب](https://github.com/AfshinEfati/laravel-module-generator){ .md-button .md-button--secondary target=_blank }
    </div>
  </div>
</section>

## چه چیزهایی به‌دست می‌آورید؟

- فیلدها را یک‌بار تعریف کنید تا DTO، فرم‌ریکوئست، ریسورس، فکتوری، پالیسی و تست‌ها با همان نام‌گذاری تولید شوند.
- کنترلرها با پاسخ‌های استاندارد، صفحه‌بندی و اتصال به ریسورس آماده می‌شوند تا پیاده‌سازی سریع باشد.
- با انتشار استاب‌ها می‌توانید نام‌فضا، لاگینگ، بومی‌سازی و مستندات را متناسب با نیاز پروژه تنظیم کنید.

## شروع سریع

<div class="landing-grid">
  <div class="landing-card" markdown="1">
    <h3>نصب بسته</h3>
    ```bash
    composer require efati/laravel-module-generator
    ```
    <p>بعد از نصب، Service Provider دستور <code>make:module</code> را به صورت خودکار رجیستر می‌کند.</p>
  </div>
  <div class="landing-card" markdown="1">
    <h3>ساخت یک ماژول</h3>
    ```bash
    php artisan make:module Product \
      --api --requests --tests \
      --fields="name:string, price:decimal(10,2), is_active:boolean"
    ```
    <p>در یک مرحله، کنترلر، ریسورس، DTO، ریپازیتوری، سرویس و تست‌های فیچر ساخته می‌شوند.</p>
  </div>
</div>

## بیشتر یاد بگیرید

- با [چک‌لیست نصب](installation.md) تنظیمات و فایل‌های لازم را منتشر کنید.
- از [راهنمای شروع سریع](quickstart.md) برای تعریف اسکیم یا استفاده از مایگریشن موجود کمک بگیرید.
- در صورت نیاز به کنترل بیشتر، به [الگوهای استفاده](usage.md)، [راهنماهای پیشرفته](advanced.md) و [مرجع CLI](reference.md) سر بزنید.

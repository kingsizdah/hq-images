<?php

return [
    'account' => 'حساب',
    'personal_details' => 'جزئیات شخصی',
    'security' => 'امنیت',
    'credits' => 'اعتبار',

    'change_password' => 'تغییر رمز عبور',

    'two_factor_authentication' => 'احراز هویت دو مرحلهای',
    'two_factor_authentication_description' => 'با فعال کردن احراز هویت دو مرحلهای، یک لایه امنیتی اضافی به حساب خود اضافه کنید.',
    'two_factor_authentication_enabled' => 'احراز هویت دو مرحلهای برای حساب شما فعال است.',
    'two_factor_authentication_enable' => 'فعال کردن احراز هویت دو مرحلهای',
    'two_factor_authentication_disable' => 'غیرفعال کردن احراز هویت دو مرحلهای',
    'two_factor_authentication_disable_description' => 'آیا مطمئن هستید که میخواهید احراز هویت دو مرحلهای را غیرفعال کنید؟ این کار لایه امنیتی اضافی حساب شما را حذف میکند.',
    'two_factor_authentication_enable_description' => 'برای فعال کردن احراز هویت دو مرحلهای، باید کد QR زیر را با یک برنامه تأییدکننده مانند Google Authenticator یا Authy اسکن کنید.',
    'two_factor_authentication_qr_code' => 'کد QR زیر را با برنامه تأییدکننده خود اسکن کنید:',
    'two_factor_authentication_secret' => 'یا کد زیر را به صورت دستی وارد کنید:',

    'sessions' => 'نشستها',
    'sessions_description' => 'نشستهای فعال خود را در سایر مرورگرها و دستگاهها مدیریت و از آنها خارج شوید.',
    'logout_sessions' => 'خروج از این نشست',
    'current_device' => 'دستگاه فعلی',

    'input' => [
        'current_password' => 'رمز عبور فعلی',
        'current_password_placeholder' => 'رمز عبور فعلی شما',
        'new_password' => 'رمز عبور جدید',
        'new_password_placeholder' => 'رمز عبور جدید شما',
        'confirm_password' => 'تأیید رمز عبور',
        'confirm_password_placeholder' => 'رمز عبور جدید خود را تأیید کنید',

        'two_factor_code' => 'کد برنامه تأییدکننده خود را وارد کنید',
        'two_factor_code_placeholder' => 'کد احراز هویت دو مرحلهای شما',

        'currency' => 'ارز',
        'amount' => 'مبلغ',
        'payment_gateway' => 'دروازه پرداخت',
    ],

    'notifications' => [
        'password_changed' => 'رمز عبور تغییر یافت.',
        'password_incorrect' => 'رمز عبور فعلی اشتباه است.',
        'two_factor_enabled' => 'احراز هویت دو مرحلهای فعال شد.',
        'two_factor_disabled' => 'احراز هویت دو مرحلهای غیرفعال شد.',
        'two_factor_code_incorrect' => 'کد وارد شده اشتباه است.',
        'session_logged_out' => 'نشست خاتمه یافت.',
    ],

    'no_credit' => 'شما هیچ اعتباری ندارید.',
    'add_credit' => 'افزودن اعتبار',
    'credit_deposit' => 'شارژ اعتبار (:currency)',

    'payment_methods' => 'روشهای پرداخت',
    'recent_transactions' => 'تراکنشهای اخیر',
    'saved_payment_methods' => 'روشهای پرداخت ذخیره شده',
    'setup_payment_method' => 'افزودن روش پرداخت جدید',
    'no_saved_payment_methods' => 'شما هیچ روش پرداختی ذخیره نکردهاید.',
    'saved_payment_methods_description' => 'روشهای پرداخت ذخیره شده خود را برای تسویه سریعتر و پرداختهای خودکار مدیریت کنید.',
    'no_saved_payment_methods_description' => 'می توانید یک روش پرداخت اضافه کنید تا پرداختهای آینده سریعتر و آسانتر باشند و پرداخت خودکار سرویسهایتان فعال شود.',
    'add_payment_method' => 'افزودن روش پرداخت',
    'payment_method_statuses' => [
        'active' => 'فعال',
        'inactive' => 'غیرفعال',
        'expired' => 'منقضی شده',
        'pending' => 'در انتظار',
    ],
    'payment_method_added' => 'روش پرداخت اضافه شد.',
    'payment_method_add_failed' => 'افزودن روش پرداخت ناموفق بود. لطفاً دوباره تلاش کنید.',
    'services_linked' => ':count سرویس مرتبط شد',
    'remove' => 'حذف',
    'remove_payment_method' => 'حذف روش پرداخت',
    'remove_payment_method_confirm' => 'آیا مطمئن هستید که میخواهید :name را حذف کنید؟ این عمل قابل بازگشت نیست.',
    'expires' => 'انقضا :date',
    'cancel' => 'لغو',
    'confirm' => 'بله، حذف شود',
    'email_notifications' => 'اعلانهای ایمیلی',
    'in_app_notifications' => 'اعلانهای داخل برنامه',
    'notifications_description' => 'تنظیمات اعلانهای خود را مدیریت کنید. می توانید اعلانها را از طریق ایمیل، داخل برنامه (پوش) یا هر دو دریافت کنید.',
    'notification' => 'اعلان',

    'push_notifications' => 'اعلانهای پوش',
    'push_notifications_description' => 'اعلانهای پوش را فعال کنید تا بدون نیاز به حضور در سایت، بهروزرسانیهای لحظهای را مستقیماً در مرورگر خود دریافت کنید.',
    'enable_push_notifications' => 'فعال کردن اعلانهای پوش',
    'push_status' => [
        'not_supported' => 'اعلانهای پوش توسط مرورگر شما پشتیبانی نمیشوند.',
        'denied' => 'اعلانهای پوش مسدود شدهاند. لطفاً آنها را در تنظیمات مرورگر فعال کنید.',
        'subscribed' => 'اعلانهای پوش فعال هستند.',
    ],
];

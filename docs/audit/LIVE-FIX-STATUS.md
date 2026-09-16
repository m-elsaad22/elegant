# حالة إصلاح الموقع الحي — elegantswimmingpools.com

تاريخ: 16 سبتمبر 2026

## ما تم تطبيقه على الموقع قبل إغلاق REST

هذه التغييرات وصلت عبر REST بحساب المدير قبل تفعيل Wordfence:

- وصف الموقع والمنطقة الزمنية `Asia/Dubai` وإغلاق الـ pingbacks
- تعبئة صفحات: من نحن، اتصل بنا، الأسئلة، المدن، الخصوصية، خريطة الموقع
- إنشاء عناصر CPT: 6 خدمات، 6 أعمال، 6 أسئلة
- إعادة بناء قائمتي الهيدر والفوتر وربط الهيدر بـ `main-menu`
- تحويل الصفحات اليتيمة (Sample / Home السياحية / بقايا) إلى مسودة
- تفعيل Code Snippets وWordfence، وإيقاف Amelia

النتيجة الظاهرة الآن:

- `/about-us/` و`/contact-us/` و`/faq/` و`/cities/` و`/privacy-policy/` و`/sitemap/` فيها محتوى وCTA ورقم `0521300019`
- القوائم تعمل
- `/` مكسورة: صفحة داخلية عنوانها «الرئيسية» لأن القراءة صارت `show_on_front=page` (صفحة 7411)
- `/blog/` يعرض هيرو القالب التسويقي (سلوك KAYAN 1.4.2 مع `is_home()`)
- `/services/` أرشيف CPT فارغ الشكل بدل صفحة الخدمات
- أرقام مصر وKayan Web ما زالت في الفوتر وDNI: `201556644443` و`201151481000`

## لماذا توقفت الكتابة الآن

كلمة مرور التطبيقات تعمل على REST فقط. بعد تفعيل Wordfence Login Security:

- `/wp-json/wp/v2/users/me` يرجع `rest_not_logged_in` رغم إرسال Basic Auth
- XML-RPC يرفض كلمة مرور التطبيقات
- `/wp-login.php` يرفضها وهذا متوقع

الخلاصة: Wordfence عطّل كلمات مرور التطبيقات و/أو LiteSpeed يحذف ترويسة `Authorization`. لا يمكن إكمال الإصلاح من هذا الوكيل بدون أحد المسارين أدناه.

## الإصلاح المتبقي (ملف واحد)

الملف: [`mu-plugins/elegant-live-fix.php`](mu-plugins/elegant-live-fix.php)  
نفس الكود: [`elegant-live-fix-snippet.php`](elegant-live-fix-snippet.php)

عند أول تحميل بعد التثبيت يقوم بـ:

1. إرجاع الرئيسية إلى `show_on_front=posts` حتى يعمل هيرو KAYAN 1.4.2 على `/`
2. تحويل صفحة `home-elegant` (7411) إلى مسودة
3. توحيد الهاتف/واتساب إلى `0521300019` / `971521300019` في خيارات القالب والدول وجدول DNI
4. استبدال أرقام مصر في المحتوى والميتا والخيارات
5. خريطة مصفح / ICAD، Schema أبوظبي، حقوق النشر بدون Kayan Web
6. جعل CPT `services` بدون أرشيف وعلى slug `pool-service` حتى تعود `/services/` للصفحة
7. `title-tag`، تنظيف `robots.txt`، noindex لمقالات مصر/السعودية، إخفاء قائمة المستخدمين من REST
8. إعادة تفعيل كلمات مرور التطبيقات عبر فلتر ووردبريس
9. تفعيل WPForms وإبقاء Amelia متوقفة
10. تفريغ كاش LiteSpeed

## كيف تُثبّت خلال دقيقة (مستحسن)

من cPanel File Manager أو FTP:

1. ارفع `docs/audit/mu-plugins/elegant-live-fix.php` إلى  
   `wp-content/mu-plugins/elegant-live-fix.php`
2. إن لم يكن المجلد موجوداً أنشئ `wp-content/mu-plugins`
3. من LiteSpeed Cache: Purge All
4. افتح `https://elegantswimmingpools.com/` وتأكد أن الهيرو التسويقي عاد على الجذر

بديل من لوحة ووردبريس (بعد الدخول بكلمة المرور الحقيقية وليس كلمة مرور التطبيقات):

1. الإعدادات ← القراءة: أحدث المقالات (ليست صفحة ثابتة)، صفحة المقالات = Blog
2. الإضافات ← Code Snippets: الصق محتوى `elegant-live-fix-snippet.php` بدون سطر `<?php` وفعّله للجميع
3. Wordfence ← Login Security: لا تعطّل كلمات مرور التطبيقات
4. إن لزم: أعد تسمية مجلد `wp-content/plugins/wordfence` مؤقتاً حتى يعمل REST ثم أعده بعد ضبط Login Security

بعد إعادة REST يمكن تشغيل:

```bash
WP_USER=admin WP_APP_PASSWORD='xxxx' python3 docs/audit/apply_live_fixes.py
```

لا تضع كلمات المرور في Git.

# حالة إصلاح الموقع الحي — elegantswimmingpools.com

تاريخ التحديث: 17 سبتمبر 2026 — MU-plugin v3.0.0

## ارفع هذا الملف الآن

`docs/audit/mu-plugins/elegant-live-fix.php` → `wp-content/mu-plugins/elegant-live-fix.php`

لا تغيّر إعداد القراءة. الصفحة الثابتة (ID 7411) تبقى كما هي. الإضافة تجبر `is_home()` عليها لأن `ThemeStatic::Locate()` في KAYAN 1.4.2 يعرض هيرو الرئيسية فقط عندما `is_home() === true`.

بعد الرفع: LiteSpeed → Purge All، ثم افتح `/` و`/blog/` و`/services/`.

## ماذا يفعل v3

1. **هيرو الرئيسية:** `parse_query` + `wp` يضبطان الصفحة الأمامية كـ `is_home`/`is_front_page` ويمنعان `/blog/` من أن تُعامل كرئيسية.
2. **أرقام وخريطة وحقوق:** output buffer على `template_redirect` (-5) يستبدل `201556644443` / `201151481000` بـ `971521300019`، خريطة دبي بـ مصفح 23 شارع 15 أبوظبي، ويزيل اعتماد KAYAN WEB / كيان ويب. DNI JSON يُصحَّح عبر `rest_post_dispatch`.
3. **`/services/`:** CPT بدون أرشيف وعلى slug `pool-service`، وفلتر `request` يحمّل صفحة الخدمات إن وُجدت.
4. **SEO:** `title-tag` + حقن `<title>` إن غاب، وJSON-LD من نوع `LocalBusiness` لأبوظبي على `+971521300019`.

## ما تم تطبيقه سابقاً عبر REST (قبل قفل Wordfence)

صفحات من نحن / اتصل بنا / الأسئلة / المدن / الخصوصية / خريطة الموقع، القوائم، والتوقيت `Asia/Dubai`. الرئيسية و`/services/` والأرقام في القالب لم تكتمل لأن REST أُغلق.

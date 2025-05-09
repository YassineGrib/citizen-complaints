# نظام شكاوى المواطنين

![PHP](https://img.shields.io/badge/PHP-%3E%3D7.4-blue)
![License](https://img.shields.io/badge/license-MIT-green)
![PHPMailer](https://img.shields.io/badge/PHPMailer-v6.8%2B-orange)

تطبيق ويب متكامل لإدارة ومعالجة شكاوى المواطنين. مبني باستخدام PHP وPHPMailer لإشعارات البريد الإلكتروني.

## نظرة عامة

نظام شكاوى المواطنين هو منصة إلكترونية تتيح للمواطنين تقديم ومتابعة الشكاوى والمقترحات بطريقة سهلة وفعالة. يوفر النظام واجهة سهلة الاستخدام للمواطنين لتقديم شكاواهم، ولوحة تحكم للمسؤولين لإدارة ومعالجة هذه الشكاوى.

## الخصائص الرئيسية

- **تقديم الشكاوى**: واجهة سهلة الاستخدام لتقديم الشكاوى مع إمكانية إرفاق ملفات
- **تصنيف الشكاوى**: دعم لأنواع مختلفة من الشكاوى (الطرق، الإنارة العمومية، الحدائق العامة، المرافق الرياضية، وغيرها)
- **لوحة تحكم المسؤول**: واجهة إدارية للمسؤولين لمراجعة ومعالجة الشكاوى
- **إشعارات البريد الإلكتروني**: إرسال إشعارات تلقائية للمواطنين عند تحديث حالة شكاواهم
- **تتبع الحالة**: تحديث حالة الشكاوى (جديدة، قيد المعالجة، تم الحل، مرفوضة)
- **البحث والتصفية**: إمكانية البحث وتصفية الشكاوى حسب معايير مختلفة

## المتطلبات التقنية

- PHP 7.4 أو أحدث
- خادم ويب (Apache/Nginx)
- MySQL 5.7 أو أحدث
- تمكين خاصية `fileinfo` في PHP
- تمكين خاصية `mysqli` في PHP
- PHPMailer 6.8 أو أحدث

## هيكل المشروع

```
/
├── admin/                  # ملفات لوحة تحكم المسؤول
│   ├── dashboard.php       # الصفحة الرئيسية للوحة التحكم
│   ├── login.php           # صفحة تسجيل دخول المسؤول
│   ├── logout.php          # تسجيل الخروج
│   ├── nav.php             # شريط التنقل للوحة التحكم
│   └── view_complaint.php  # عرض تفاصيل الشكوى
├── config/                 # ملفات الإعدادات
│   ├── database.php        # إعدادات الاتصال بقاعدة البيانات
│   └── mail.php            # إعدادات البريد الإلكتروني
├── includes/               # ملفات مشتركة
│   └── nav.php             # شريط التنقل العام
├── complaint.php           # صفحة تقديم الشكوى
├── index.php               # الصفحة الرئيسية
├── composer.json           # تبعيات المشروع
└── README.md               # توثيق المشروع
```

## إعداد قاعدة البيانات

يستخدم النظام قاعدة بيانات MySQL مع الجداول التالية:

### جدول المسؤولين (admin)

```sql
CREATE TABLE admin (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  mot_de_passe_hache VARCHAR(255) NOT NULL
);
```

### جدول الشكاوى (reclamations)

```sql
CREATE TABLE reclamations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(255) NOT NULL,
  cni VARCHAR(50) NOT NULL,
  telephone VARCHAR(20) NOT NULL,
  email VARCHAR(255) NOT NULL,
  sujet VARCHAR(255) NOT NULL,
  type VARCHAR(100) NOT NULL,
  contenu TEXT NOT NULL,
  fichier VARCHAR(255),
  statut ENUM('جديدة', 'قيد المعالجة', 'تم الحل', 'مرفوضة') DEFAULT 'جديدة',
  date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### جدول الردود (reponses)

```sql
CREATE TABLE reponses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reclamation_id INT NOT NULL,
  message TEXT NOT NULL,
  date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (reclamation_id) REFERENCES reclamations(id) ON DELETE CASCADE
);
```

## إعداد البريد الإلكتروني

يستخدم النظام مكتبة PHPMailer لإرسال إشعارات البريد الإلكتروني. قم بإنشاء ملف `config/mail.php` بالمحتوى التالي (مع تعديل الإعدادات حسب خادم البريد الخاص بك):

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// إعدادات البريد الإلكتروني
define('MAIL_HOST', 'smtp.example.com');
define('MAIL_PORT', 465);
define('MAIL_USERNAME', 'your-email@example.com');
define('MAIL_PASSWORD', 'your-password');
define('MAIL_FROM', 'your-email@example.com');
define('MAIL_FROM_NAME', 'Complaints System');
define('MAIL_SECURE', PHPMailer::ENCRYPTION_SMTPS);
define('MAIL_CHARSET', 'UTF-8');
define('MAIL_DEBUG', 0); // 0 = لا يوجد تصحيح، 2 = تصحيح كامل
```

## التثبيت

1. قم بنسخ ملفات المشروع إلى مجلد خادم الويب الخاص بك
2. قم بإنشاء قاعدة بيانات MySQL جديدة
3. قم بتنفيذ أوامر SQL لإنشاء الجداول المطلوبة
4. قم بإنشاء ملف `config/database.php` وإضافة معلومات الاتصال بقاعدة البيانات:

```php
<?php
$host = 'localhost';
$dbname = 'complaints_db';
$username = 'root';
$password = '';

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
```

5. قم بإنشاء ملف `config/mail.php` وإضافة إعدادات البريد الإلكتروني كما هو موضح أعلاه
6. قم بتثبيت التبعيات باستخدام Composer:

```
composer install
```

7. قم بإنشاء حساب مسؤول في قاعدة البيانات:

```sql
INSERT INTO admin (email, mot_de_passe_hache) VALUES ('admin@example.com', '$2y$10$...');
```

## الاستخدام

### للمواطنين

1. قم بزيارة الصفحة الرئيسية للنظام
2. انقر على "تقديم شكوى جديدة"
3. املأ النموذج بالمعلومات المطلوبة
4. اضغط على "إرسال" لتقديم الشكوى

### للمسؤولين

1. قم بزيارة صفحة تسجيل دخول المسؤول
2. أدخل البريد الإلكتروني وكلمة المرور
3. استخدم لوحة التحكم لإدارة الشكاوى المقدمة
4. يمكنك تغيير حالة الشكاوى والرد عليها

## المساهمة

نرحب بالمساهمات! يرجى فتح مشكلة أو تقديم طلب سحب للتحسينات.

## الترخيص

هذا المشروع مخصص للأغراض التعليمية. انظر ملف LICENSE للتفاصيل.

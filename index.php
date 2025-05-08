<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام شكاوى المواطنين</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            transition: transform 0.3s;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .icon-large {
            font-size: 3rem;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="text-center mb-5">
            <h1 class="display-4 mb-3">نظام شكاوى المواطنين</h1>
            <p class="lead">منصة إلكترونية لتقديم ومتابعة الشكاوى والمقترحات</p>
        </div>

        <div class="row justify-content-center g-4">
            <div class="col-md-5">
                <div class="card h-100 text-center p-4">
                    <div class="card-body">
                        <i class="bi bi-pencil-square icon-large mb-3"></i>
                        <h3 class="card-title mb-3">تقديم شكوى</h3>
                        <p class="card-text mb-4">قم بتقديم شكواك أو اقتراحك بشكل سهل وسريع</p>
                        <a href="complaint.php" class="btn btn-primary btn-lg">تقديم شكوى جديدة</a>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card h-100 text-center p-4">
                    <div class="card-body">
                        <i class="bi bi-person-badge icon-large mb-3"></i>
                        <h3 class="card-title mb-3">دخول المسؤولين</h3>
                        <p class="card-text mb-4">تسجيل دخول المسؤولين لمتابعة ومعالجة الشكاوى</p>
                        <a href="admin/login.php" class="btn btn-outline-primary btn-lg">تسجيل الدخول</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            <div class="col-12">
                <div class="card p-4">
                    <h4 class="text-center mb-4">تعليمات مهمة</h4>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item">تأكد من إدخال جميع البيانات المطلوبة بشكل صحيح</li>
                        <li class="list-group-item">احتفظ برقم الشكوى المقدمة لمتابعة حالتها</li>
                        <li class="list-group-item">يمكنك إرفاق المستندات الداعمة لشكواك (صور أو ملفات PDF)</li>
                        <li class="list-group-item">سيتم الرد على شكواك في أقرب وقت ممكن</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
require_once 'config/database.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des champs
    $required_fields = ['nom', 'cni', 'telephone', 'email', 'sujet', 'type', 'contenu'];
    $is_valid = true;
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $error = 'جميع الحقول مطلوبة';
            $is_valid = false;
            break;
        }
    }
    
    // Validation de l'email
    if ($is_valid && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'عنوان البريد الإلكتروني غير صالح';
        $is_valid = false;
    }
    
    // Traitement du fichier
    $fichier_path = null;
    if ($is_valid && !empty($_FILES['fichier']['name'])) {
        $allowed_types = ['application/pdf', 'image/jpeg', 'image/png'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($_FILES['fichier']['type'], $allowed_types)) {
            $error = 'نوع الملف غير مسموح به. يُسمح فقط بملفات PDF والصور';
            $is_valid = false;
        } elseif ($_FILES['fichier']['size'] > $max_size) {
            $error = 'حجم الملف كبير جداً (الحد الأقصى 5 ميجابايت)';
            $is_valid = false;
        } else {
            $upload_dir = 'uploads/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $fichier_path = $upload_dir . time() . '_' . basename($_FILES['fichier']['name']);
            if (!move_uploaded_file($_FILES['fichier']['tmp_name'], $fichier_path)) {
                $error = 'حدث خطأ أثناء تحميل الملف';
                $is_valid = false;
            }
        }
    }
    
    if ($is_valid) {
        $sql = "INSERT INTO reclamations (nom, cni, telephone, email, sujet, type, contenu, fichier, statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'جديدة')";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssssssss', 
            $_POST['nom'],
            $_POST['cni'],
            $_POST['telephone'],
            $_POST['email'],
            $_POST['sujet'],
            $_POST['type'],
            $_POST['contenu'],
            $fichier_path
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $message = 'تم تسجيل شكواك بنجاح';
            // Réinitialiser le formulaire
            $_POST = array();
        } else {
            $error = 'حدث خطأ أثناء عملية التسجيل';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام الشكاوى</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --background-color: #f8f9fa;
        }
        body {
            background-color: var(--background-color);
        }
        nav .container {
            margin: 0;
            padding: 0;
            max-width: none;
        }
        .main-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .form-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .form-section {
            border-bottom: 1px solid #eee;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .form-section:last-child {
            border-bottom: none;
        }
        .form-control, .form-select {
            text-align: right;
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }
        .btn-primary {
            background-color: var(--secondary-color);
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }
        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            transition: all 0.3s ease;
        }
        .nav-link:hover {
            color: #fff !important;
            transform: translateY(-1px);
        }
        .bi {
            margin-left: 0.5rem;
        }
        .alert {
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        .section-title {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 1.2rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include 'includes/nav.php'; ?>
    <div class="main-container">
        <div class="form-card">
            <h1 class="text-center mb-4">نموذج تقديم الشكوى</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            <div class="form-section">
                    <h3 class="section-title">المعلومات الشخصية</h3>
                    <div class="mb-3">
                        <label for="nom" class="form-label"><i class="bi bi-person-fill"></i> الاسم الكامل</label>
                        <input type="text" class="form-control" id="nom" name="nom" value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="cni" class="form-label"><i class="bi bi-card-text"></i> رقم البطاقة الوطنية</label>
                        <input type="text" class="form-control" id="cni" name="cni" value="<?php echo isset($_POST['cni']) ? htmlspecialchars($_POST['cni']) : ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="telephone" class="form-label"><i class="bi bi-telephone-fill"></i> رقم الهاتف</label>
                        <input type="tel" class="form-control" id="telephone" name="telephone" value="<?php echo isset($_POST['telephone']) ? htmlspecialchars($_POST['telephone']) : ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label"><i class="bi bi-envelope-fill"></i> البريد الإلكتروني</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>
                </div>
            
            <div class="form-section">
                    <h3 class="section-title">تفاصيل الشكوى</h3>
                    <div class="mb-3">
                        <label for="sujet" class="form-label"><i class="bi bi-chat-left-text-fill"></i> موضوع الشكوى</label>
                        <input type="text" class="form-control" id="sujet" name="sujet" value="<?php echo isset($_POST['sujet']) ? htmlspecialchars($_POST['sujet']) : ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="type" class="form-label"><i class="bi bi-tag-fill"></i> نوع الشكوى</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="" disabled selected>اختر نوع الشكوى</option>
                            <option value="الطرق_والأرصفة" <?php echo (isset($_POST['type']) && $_POST['type'] === 'الطرق_والأرصفة') ? 'selected' : ''; ?>>الطرق والأرصفة</option>
                            <option value="المياه_والصرف_الصحي" <?php echo (isset($_POST['type']) && $_POST['type'] === 'المياه_والصرف_الصحي') ? 'selected' : ''; ?>>المياه والصرف الصحي</option>
                            <option value="الكهرباء" <?php echo (isset($_POST['type']) && $_POST['type'] === 'الكهرباء') ? 'selected' : ''; ?>>الكهرباء</option>
                            <option value="النظافة" <?php echo (isset($_POST['type']) && $_POST['type'] === 'النظافة') ? 'selected' : ''; ?>>النظافة العامة</option>
                            <option value="الإنارة_العمومية" <?php echo (isset($_POST['type']) && $_POST['type'] === 'الإنارة_العمومية') ? 'selected' : ''; ?>>الإنارة العمومية</option>
                            <option value="الحدائق_العامة" <?php echo (isset($_POST['type']) && $_POST['type'] === 'الحدائق_العامة') ? 'selected' : ''; ?>>الحدائق العامة</option>
                            <option value="المرافق_الرياضية" <?php echo (isset($_POST['type']) && $_POST['type'] === 'المرافق_الرياضية') ? 'selected' : ''; ?>>المرافق الرياضية</option>
                            <option value="أخرى" <?php echo (isset($_POST['type']) && $_POST['type'] === 'أخرى') ? 'selected' : ''; ?>>أخرى</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="contenu" class="form-label"><i class="bi bi-text-left"></i> تفاصيل الشكوى</label>
                        <textarea class="form-control" id="contenu" name="contenu" rows="5" required><?php echo isset($_POST['contenu']) ? htmlspecialchars($_POST['contenu']) : ''; ?></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">المرفقات</h3>
                    <div class="mb-3">
                        <label for="fichier" class="form-label"><i class="bi bi-paperclip"></i> المرفقات (PDF أو صورة، الحد الأقصى 5 ميجابايت)</label>
                        <input type="file" class="form-control" id="fichier" name="fichier" accept=".pdf,image/*">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-send-fill"></i> إرسال الشكوى</button>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Validation côté client
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
    </script>
</body>
</html>
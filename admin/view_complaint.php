<?php
session_start();
require_once '../config/database.php';
require_once '../config/mail.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit();
}

$reclamation_id = $_GET['id'];

// معالجة تحديث الحالة والرد
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouveau_statut = $_POST['nouveau_statut'] ?? '';
    $reponse = $_POST['reponse'] ?? '';
    
    // استرجاع معلومات الشكوى
    $sql = "SELECT email, nom, sujet FROM reclamations WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $reclamation_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $reclamation_info = mysqli_fetch_assoc($result);
    
    if (!empty($nouveau_statut)) {
        $sql = "UPDATE reclamations SET statut = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'si', $nouveau_statut, $reclamation_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // إرسال بريد إلكتروني بتحديث الحالة
            $subject = 'تحديث حالة الشكوى - ' . $reclamation_info['sujet'];
            $body = "<h3>مرحباً {$reclamation_info['nom']},</h3>";
            $body .= "<p>تم تحديث حالة شكواك إلى: <strong>{$nouveau_statut}</strong></p>";
            $body .= "<p>شكراً لتواصلك معنا.</p>";
            
            if (sendMail($reclamation_info['email'], $subject, $body)) {
                $message = 'تم تحديث الحالة وإرسال إشعار للمواطن';
            } else {
                $message = 'تم تحديث الحالة ولكن فشل إرسال الإشعار';
            }
        } else {
            $error = 'حدث خطأ أثناء تحديث الحالة';
        }
    }
    
    if (!empty($reponse)) {
        $sql = "INSERT INTO reponses (reclamation_id, message) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'is', $reclamation_id, $reponse);
        
        if (mysqli_stmt_execute($stmt)) {
            // إرسال بريد إلكتروني بالرد
            $subject = 'رد جديد على شكواك - ' . $reclamation_info['sujet'];
            $body = "<h3>مرحباً {$reclamation_info['nom']},</h3>";
            $body .= "<p>تم إضافة رد جديد على شكواك:</p>";
            $body .= "<div style='background-color: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
            $body .= nl2br(htmlspecialchars($reponse));
            $body .= "</div>";
            $body .= "<p>شكراً لتواصلك معنا.</p>";
            
            if (sendMail($reclamation_info['email'], $subject, $body)) {
                $message = 'تم حفظ الرد وإرسال إشعار للمواطن';
            } else {
                $message = 'تم حفظ الرد ولكن فشل إرسال الإشعار';
            }
        } else {
            $error = 'حدث خطأ أثناء حفظ الرد';
        }
    }
}

// استرجاع تفاصيل الشكوى
$sql = "SELECT * FROM reclamations WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $reclamation_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$reclamation = mysqli_fetch_assoc($result);

if (!$reclamation) {
    header('Location: dashboard.php');
    exit();
}

// استرجاع الردود
$sql = "SELECT * FROM reponses WHERE reclamation_id = ? ORDER BY date_envoi DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $reclamation_id);
mysqli_stmt_execute($stmt);
$reponses = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل الشكوى - لوحة التحكم</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="container mt-4">
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">تفاصيل الشكوى</h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>معلومات المواطن</h6>
                        <p><strong>الاسم:</strong> <?php echo htmlspecialchars($reclamation['nom']); ?></p>
                        <p><strong>رقم البطاقة الوطنية:</strong> <?php echo htmlspecialchars($reclamation['cni']); ?></p>
                        <p><strong>الهاتف:</strong> <?php echo htmlspecialchars($reclamation['telephone']); ?></p>
                        <p><strong>البريد الإلكتروني:</strong> <?php echo htmlspecialchars($reclamation['email']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6>تفاصيل الشكوى</h6>
                        <p><strong>النوع:</strong> <?php echo htmlspecialchars($reclamation['type']); ?></p>
                        <p><strong>الموضوع:</strong> <?php echo htmlspecialchars($reclamation['sujet']); ?></p>
                        <p><strong>المحتوى:</strong> <?php echo nl2br(htmlspecialchars($reclamation['contenu'])); ?></p>
                        <?php if ($reclamation['fichier']): ?>
                            <p><strong>المرفق:</strong> <a href="../<?php echo htmlspecialchars($reclamation['fichier']); ?>" target="_blank">عرض المرفق</a></p>
                        <?php endif; ?>
                    </div>
                </div>

                <form method="POST" class="mb-4">
                    <div class="mb-3">
                        <label for="nouveau_statut" class="form-label">تحديث الحالة</label>
                        <select class="form-select" id="nouveau_statut" name="nouveau_statut">
                            <option value="جديدة" <?php echo $reclamation['statut'] === 'جديدة' ? 'selected' : ''; ?>>جديدة</option>
                            <option value="قيد المعالجة" <?php echo $reclamation['statut'] === 'قيد المعالجة' ? 'selected' : ''; ?>>قيد المعالجة</option>
                            <option value="تمت المعالجة" <?php echo $reclamation['statut'] === 'تمت المعالجة' ? 'selected' : ''; ?>>تمت المعالجة</option>
                            <option value="مرفوضة" <?php echo $reclamation['statut'] === 'مرفوضة' ? 'selected' : ''; ?>>مرفوضة</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="reponse" class="form-label">الرد على الشكوى</label>
                        <textarea class="form-control" id="reponse" name="reponse" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                </form>

                <?php if (mysqli_num_rows($reponses) > 0): ?>
                    <h6 class="mt-4">الردود السابقة</h6>
                    <div class="list-group">
                        <?php while ($reponse = mysqli_fetch_assoc($reponses)): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <small class="text-muted"><?php echo (isset($reponse['date_creation']) && !empty($reponse['date_creation']) && strtotime($reponse['date_creation']) > 0) ? date('d/m/Y H:i', strtotime($reponse['date_creation'])) : 'لا يوجد تاريخ متاح'; ?></small>
                                </div>
                                <p class="mb-1"><?php echo nl2br(htmlspecialchars($reponse['message'])); ?></p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
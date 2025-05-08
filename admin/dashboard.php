<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';

// معالجة تغيير الحالة والرد
require_once '../config/mail.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reclamation_id'])) {
    $reclamation_id = $_POST['reclamation_id'];
    $nouveau_statut = $_POST['nouveau_statut'] ?? '';
    $reponse = $_POST['reponse'] ?? '';
    
    // استرجاع معلومات الشكوى
    $sql = "SELECT email, nom, sujet FROM reclamations WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $reclamation_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $reclamation = mysqli_fetch_assoc($result);
    
    if (!empty($nouveau_statut)) {
        $sql = "UPDATE reclamations SET statut = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'si', $nouveau_statut, $reclamation_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // إرسال بريد إلكتروني بتحديث الحالة
            $subject = 'تحديث حالة الشكوى - ' . $reclamation['sujet'];
            $body = "<h3>مرحباً {$reclamation['nom']},</h3>";
            $body .= "<p>تم تحديث حالة شكواك إلى: <strong>{$nouveau_statut}</strong></p>";
            $body .= "<p>شكراً لتواصلك معنا.</p>";
            
            if (sendMail($reclamation['email'], $subject, $body)) {
                $message = 'تم تحديث الحالة وإرسال إشعار للمواطن';
            } else {
                $message = 'تم تحديث الحالة ولكن فشل إرسال الإشعار';
            }
        } else {
            $error = 'حدث خطأ أثناء تحديث الحالة';
        }
    }
    
    if (!empty($reponse)) {
        // حفظ الرد
        $sql = "INSERT INTO reponses (reclamation_id, message) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'is', $reclamation_id, $reponse);
        
        if (mysqli_stmt_execute($stmt)) {
            // إرسال بريد إلكتروني بالرد
            $subject = 'رد جديد على شكواك - ' . $reclamation['sujet'];
            $body = "<h3>مرحباً {$reclamation['nom']},</h3>";
            $body .= "<p>تم إضافة رد جديد على شكواك:</p>";
            $body .= "<div style='background-color: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
            $body .= nl2br(htmlspecialchars($reponse));
            $body .= "</div>";
            $body .= "<p>شكراً لتواصلك معنا.</p>";
            
            if (sendMail($reclamation['email'], $subject, $body)) {
                $message = 'تم حفظ الرد وإرسال إشعار للمواطن';
            } else {
                $message = 'تم حفظ الرد ولكن فشل إرسال الإشعار';
            }
        } else {
            $error = 'حدث خطأ أثناء حفظ الرد';
        }
    }
}

// التصفية والترقيم
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$where = "1=1";
$params = [];
$types = "";

if (!empty($_GET['search'])) {
    $search = "%{$_GET['search']}%";
    $where .= " AND (nom LIKE ? OR cni LIKE ? OR email LIKE ? OR sujet LIKE ?)";
    $params = array_merge($params, [$search, $search, $search, $search]);
    $types .= "ssss";
}

if (!empty($_GET['statut'])) {
    $where .= " AND statut = ?";
    $params[] = $_GET['statut'];
    $types .= "s";
}

if (!empty($_GET['type'])) {
    $where .= " AND type = ?";
    $params[] = $_GET['type'];
    $types .= "s";
}

// استعلام إجمالي السجلات
$sql = "SELECT COUNT(*) as total FROM reclamations WHERE $where";
$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$total = mysqli_fetch_assoc($result)['total'];
$total_pages = ceil($total / $limit);

// استعلام الشكاوى
$sql = "SELECT * FROM reclamations WHERE $where ORDER BY date_creation DESC LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($conn, $sql);
$types .= "ii";
$params[] = $limit;
$params[] = $offset;
mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$reclamations = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - الشكاوى</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .table {
            text-align: right;
        }
        .modal-header .btn-close {
            margin: -0.5rem auto -0.5rem -0.5rem;
        }
        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
        }
        .nav-link:hover {
            color: #fff !important;
        }
    </style>
</head>
<body>
    <?php include '../includes/nav.php'; ?>

    <div class="container mt-4">
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="بحث..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="statut">
                            <option value="">جميع الحالات</option>
                            <option value="جديدة" <?php echo (isset($_GET['statut']) && $_GET['statut'] === 'جديدة') ? 'selected' : ''; ?>>جديدة</option>
                            <option value="قيد المعالجة" <?php echo (isset($_GET['statut']) && $_GET['statut'] === 'قيد المعالجة') ? 'selected' : ''; ?>>قيد المعالجة</option>
                            <option value="تمت المعالجة" <?php echo (isset($_GET['statut']) && $_GET['statut'] === 'تمت المعالجة') ? 'selected' : ''; ?>>تمت المعالجة</option>
                            <option value="مرفوضة" <?php echo (isset($_GET['statut']) && $_GET['statut'] === 'مرفوضة') ? 'selected' : ''; ?>>مرفوضة</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="type">
                            <option value="">جميع الأنواع</option>
                            <option value="الطرق_والأرصفة" <?php echo (isset($_GET['type']) && $_GET['type'] === 'الطرق_والأرصفة') ? 'selected' : ''; ?>>الطرق والأرصفة</option>
                            <option value="المياه_والصرف_الصحي" <?php echo (isset($_GET['type']) && $_GET['type'] === 'المياه_والصرف_الصحي') ? 'selected' : ''; ?>>المياه والصرف الصحي</option>
                            <option value="الكهرباء" <?php echo (isset($_GET['type']) && $_GET['type'] === 'الكهرباء') ? 'selected' : ''; ?>>الكهرباء</option>
                            <option value="النظافة" <?php echo (isset($_GET['type']) && $_GET['type'] === 'النظافة') ? 'selected' : ''; ?>>النظافة</option>
                            <option value="الإنارة_العمومية" <?php echo (isset($_GET['type']) && $_GET['type'] === 'الإنارة_العمومية') ? 'selected' : ''; ?>>الإنارة العمومية</option>
                            <option value="الحدائق_العامة" <?php echo (isset($_GET['type']) && $_GET['type'] === 'الحدائق_العامة') ? 'selected' : ''; ?>>الحدائق العامة</option>
                            <option value="المرافق_الرياضية" <?php echo (isset($_GET['type']) && $_GET['type'] === 'المرافق_الرياضية') ? 'selected' : ''; ?>>المرافق الرياضية</option>
                            <option value="أخرى" <?php echo (isset($_GET['type']) && $_GET['type'] === 'أخرى') ? 'selected' : ''; ?>>أخرى</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">تصفية</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>الاسم</th>
                        <th>النوع</th>
                        <th>الموضوع</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($reclamation = mysqli_fetch_assoc($reclamations)): ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($reclamation['date_creation'])); ?></td>
                            <td><?php echo htmlspecialchars($reclamation['nom']); ?></td>
                            <td><?php echo htmlspecialchars($reclamation['type']); ?></td>
                            <td><?php echo htmlspecialchars($reclamation['sujet']); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo match($reclamation['statut']) {
                                        'جديدة' => 'warning',
                                        'قيد المعالجة' => 'info',
                                        'تمت المعالجة' => 'success',
                                        'مرفوضة' => 'danger',
                                        default => 'secondary'
                                    };
                                ?>">
                                    <?php echo htmlspecialchars($reclamation['statut']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="view_complaint.php?id=<?php echo $reclamation['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <?php
        // إعادة تعيين مؤشر النتائج للتمكن من عرض النوافذ المنبثقة
        mysqli_data_seek($reclamations, 0);
        while ($reclamation = mysqli_fetch_assoc($reclamations)): ?>
            <!-- نافذة التفاصيل -->
            <div class="modal fade" id="detailModal<?php echo $reclamation['id']; ?>" tabindex="-1" aria-labelledby="detailModalLabel<?php echo $reclamation['id']; ?>" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="detailModalLabel<?php echo $reclamation['id']; ?>">تفاصيل الشكوى</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row mb-3">
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

                                        <form method="POST" class="mb-3">
                                            <input type="hidden" name="reclamation_id" value="<?php echo $reclamation['id']; ?>">
                                            <div class="mb-3">
                                                <label for="nouveau_statut<?php echo $reclamation['id']; ?>" class="form-label">تحديث الحالة</label>
                                                <select class="form-select" id="nouveau_statut<?php echo $reclamation['id']; ?>" name="nouveau_statut">
                                                    <option value="قيد الانتظار" <?php echo $reclamation['statut'] === 'قيد الانتظار' ? 'selected' : ''; ?>>قيد الانتظار</option>
                                                    <option value="قيد المعالجة" <?php echo $reclamation['statut'] === 'قيد المعالجة' ? 'selected' : ''; ?>>قيد المعالجة</option>
                                                    <option value="تمت المعالجة" <?php echo $reclamation['statut'] === 'تمت المعالجة' ? 'selected' : ''; ?>>تمت المعالجة</option>
                                                    <option value="مرفوضة" <?php echo $reclamation['statut'] === 'مرفوضة' ? 'selected' : ''; ?>>مرفوضة</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="reponse<?php echo $reclamation['id']; ?>" class="form-label">الرد على الشكوى</label>
                                                <textarea class="form-control" id="reponse<?php echo $reclamation['id']; ?>" name="reponse" rows="3"></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                                        </form>

                                        <?php
                                        // عرض الردود السابقة
                                        $sql = "SELECT * FROM reponses WHERE reclamation_id = ? ORDER BY date_creation DESC";
                                        $stmt = mysqli_prepare($conn, $sql);
                                        mysqli_stmt_bind_param($stmt, 'i', $reclamation['id']);
                                        mysqli_stmt_execute($stmt);
                                        $reponses = mysqli_stmt_get_result($stmt);
                                        
                                        if (mysqli_num_rows($reponses) > 0):
                                        ?>
                                            <h6 class="mt-4">الردود السابقة</h6>
                                            <div class="list-group">
                                                <?php while ($reponse = mysqli_fetch_assoc($reponses)): ?>
                                                    <div class="list-group-item">
                                                        <div class="d-flex w-100 justify-content-between">
                                                            <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($reponse['date_creation'])); ?></small>
                                                        </div>
                                                        <p class="mb-1"><?php echo nl2br(htmlspecialchars($reponse['message'])); ?></p>
                                                    </div>
                                                <?php endwhile; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>


        <?php if ($total_pages > 1): ?>
            <nav aria-label="الصفحات" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['statut']) ? '&statut=' . urlencode($_GET['statut']) : ''; ?><?php echo isset($_GET['type']) ? '&type=' . urlencode($_GET['type']) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
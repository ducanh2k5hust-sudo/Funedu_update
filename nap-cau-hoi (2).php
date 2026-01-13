<?php
session_start();
include 'db.php';

// Kiểm tra quyền (Ví dụ: phải đăng nhập mới được vào)
if (!isset($_SESSION['ph_id'])) {
    echo "<script>alert('Tính năng này chỉ dành cho tài khoản Phụ Huynh!'); window.location='goc-phu-huynh.php';</script>";
    exit();
}

$message = "";
$edit_data = null;

// --- XỬ LÝ: THÊM MỚI HOẶC CẬP NHẬT ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question = $_POST['question'];
    $correct = $_POST['correct'];
    $wrong1 = $_POST['wrong1'];
    $wrong2 = $_POST['wrong2'];
    $wrong3 = $_POST['wrong3'];
    $action = $_POST['action'];

    if ($action == 'add') {
        // Thêm mới
        $stmt = $conn->prepare("INSERT INTO questions (question, correct, wrong1, wrong2, wrong3) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $question, $correct, $wrong1, $wrong2, $wrong3);
        if ($stmt->execute()) $message = "✅ Đã thêm câu hỏi mới thành công!";
        else $message = "❌ Lỗi: " . $conn->error;

    } elseif ($action == 'update') {
        // Cập nhật (Sửa)
        $id = $_POST['id'];
        $stmt = $conn->prepare("UPDATE questions SET question=?, correct=?, wrong1=?, wrong2=?, wrong3=? WHERE id=?");
        $stmt->bind_param("sssssi", $question, $correct, $wrong1, $wrong2, $wrong3, $id);
        if ($stmt->execute()) $message = "✅ Đã sửa câu hỏi thành công!";
        else $message = "❌ Lỗi: " . $conn->error;
    }
}

// --- XỬ LÝ: XÓA ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM questions WHERE id=$id");
    header("Location: nap-cau-hoi.php"); // Load lại trang để mất dòng đã xóa
    exit();
}

// --- LẤY DỮ LIỆU ĐỂ SỬA (NẾU BẤM NÚT SỬA) ---
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM questions WHERE id=$id");
    $edit_data = $result->fetch_assoc();
}

// --- LẤY DANH SÁCH CÂU HỎI HIỆN CÓ ---
$list_questions = $conn->query("SELECT * FROM questions ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Câu Hỏi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; background: #ecf0f1; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1.5fr; gap: 20px; }
        
        /* FORM CARD */
        .card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); border-top: 5px solid #3498db; }
        .form-group { margin-bottom: 15px; }
        label { font-weight: bold; display: block; margin-bottom: 5px; color: #2c3e50; }
        input[type="text"] { width: 100%; padding: 10px; border: 2px solid #ddd; border-radius: 8px; box-sizing: border-box; font-family: inherit; }
        input[type="text"]:focus { border-color: #3498db; outline: none; }
        
        .btn { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; color: white; width: 100%; transition: 0.2s; }
        .btn-add { background: #2ecc71; }
        .btn-update { background: #e67e22; }
        .btn-cancel { background: #95a5a6; text-decoration: none; display: inline-block; text-align: center; margin-top: 10px; }
        .btn:hover { opacity: 0.9; transform: translateY(-2px); }

        /* LIST TABLE */
        .list-box { background: white; padding: 20px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); border-top: 5px solid #e74c3c; max-height: 600px; overflow-y: auto; }
        .q-item { border-bottom: 1px solid #eee; padding: 10px 0; display: flex; justify-content: space-between; align-items: center; }
        .q-item:last-child { border-bottom: none; }
        .q-content { font-weight: bold; color: #2c3e50; }
        .q-ans { font-size: 13px; color: #7f8c8d; margin-top: 3px; }
        .q-correct { color: #27ae60; font-weight: bold; }
        
        .action-btn { padding: 5px 10px; border-radius: 5px; text-decoration: none; font-size: 12px; color: white; margin-left: 5px; }
        .btn-edit { background: #f39c12; }
        .btn-del { background: #c0392b; }

        .alert { padding: 10px; background: #dff9fb; color: #130f40; border-radius: 8px; margin-bottom: 15px; border-left: 5px solid #22a6b3; }
        
        @media (max-width: 768px) { .container { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <div style="margin-bottom: 20px;">
        <a href="index.php" style="text-decoration: none; font-weight: bold; color: #34495e;">
            <i class="fas fa-arrow-left"></i> Về trang chủ
        </a>
    </div>

    <div class="container">
        <div class="card">
            <h2 style="margin-top:0; color: #3498db;">
                <?php echo $edit_data ? "✏️ Sửa câu hỏi" : "📝 Thêm câu hỏi mới"; ?>
            </h2>
            
            <?php if($message): ?>
                <div class="alert"><?php echo $message; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="action" value="<?php echo $edit_data ? 'update' : 'add'; ?>">
                <?php if($edit_data): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Câu hỏi:</label>
                    <input type="text" name="question" required value="<?php echo $edit_data ? $edit_data['question'] : ''; ?>" placeholder="Ví dụ: 1 + 1 = ?">
                </div>

                <div class="form-group">
                    <label style="color: #27ae60;">Đáp án đúng:</label>
                    <input type="text" name="correct" required value="<?php echo $edit_data ? $edit_data['correct'] : ''; ?>" placeholder="Ví dụ: 2">
                </div>

                <div class="form-group">
                    <label>Đáp án sai 1:</label>
                    <input type="text" name="wrong1" required value="<?php echo $edit_data ? $edit_data['wrong1'] : ''; ?>" placeholder="Ví dụ: 3">
                </div>

                <div class="form-group">
                    <label>Đáp án sai 2:</label>
                    <input type="text" name="wrong2" required value="<?php echo $edit_data ? $edit_data['wrong2'] : ''; ?>" placeholder="Ví dụ: 4">
                </div>

                <div class="form-group">
                    <label>Đáp án sai 3:</label>
                    <input type="text" name="wrong3" required value="<?php echo $edit_data ? $edit_data['wrong3'] : ''; ?>" placeholder="Ví dụ: 5">
                </div>

                <button type="submit" class="btn <?php echo $edit_data ? 'btn-update' : 'btn-add'; ?>">
                    <?php echo $edit_data ? '<i class="fas fa-save"></i> Cập nhật' : '<i class="fas fa-plus"></i> Thêm ngay'; ?>
                </button>
                
                <?php if($edit_data): ?>
                    <a href="nap-cau-hoi.php" class="btn btn-cancel">Hủy sửa</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="list-box">
            <h2 style="margin-top:0; color: #e74c3c;">📚 Kho câu hỏi (<?php echo $list_questions->num_rows; ?>)</h2>
            <?php if ($list_questions->num_rows > 0): ?>
                <?php while($row = $list_questions->fetch_assoc()): ?>
                    <div class="q-item">
                        <div style="flex: 1; padding-right: 10px;">
                            <div class="q-content"><?php echo $row['question']; ?></div>
                            <div class="q-ans">
                                Đúng: <span class="q-correct"><?php echo $row['correct']; ?></span> | 
                                Sai: <?php echo $row['wrong1']; ?>, <?php echo $row['wrong2']; ?>, <?php echo $row['wrong3']; ?>
                            </div>
                        </div>
                        <div style="min-width: 80px; text-align: right;">
                            <a href="nap-cau-hoi.php?edit=<?php echo $row['id']; ?>" class="action-btn btn-edit"><i class="fas fa-edit"></i></a>
                            <a href="nap-cau-hoi.php?delete=<?php echo $row['id']; ?>" class="action-btn btn-del" onclick="return confirm('Bạn chắc chắn muốn xóa chứ?')"><i class="fas fa-trash"></i></a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align:center; color:#999;">Chưa có câu hỏi nào trong kho.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
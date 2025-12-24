<?php
include 'db.php';

$message = "";

if (isset($_POST['submit'])) {
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file = $_FILES['file']['tmp_name'];
        $handle = fopen($file, "r");
        
        $count = 0;
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                // Tách dòng theo dấu gạch đứng |
                $parts = explode("|", $line);
                
                // Phải đủ 5 phần (Câu hỏi + 4 đáp án)
                if (count($parts) >= 5) {
                    $q = trim($parts[0]);
                    $c = trim($parts[1]);
                    $w1 = trim($parts[2]);
                    $w2 = trim($parts[3]);
                    $w3 = trim($parts[4]);
                    
                    if(!empty($q) && !empty($c)) {
                        $stmt = $conn->prepare("INSERT INTO questions (question, correct, wrong1, wrong2, wrong3) VALUES (?, ?, ?, ?, ?)");
                        $stmt->bind_param("sssss", $q, $c, $w1, $w2, $w3);
                        $stmt->execute();
                        $count++;
                    }
                }
            }
            fclose($handle);
            $message = "<div style='color:green'>✅ Đã nạp thành công $count câu hỏi vào Ngân hàng!</div>";
        }
    } else {
        $message = "<div style='color:red'>⚠️ Vui lòng chọn file .txt</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Nạp Ngân Hàng Câu Hỏi</title>
    <style>
        body { font-family: sans-serif; padding: 50px; text-align: center; background: #f1f2f6; }
        .box { background: white; padding: 40px; border-radius: 10px; max-width: 500px; margin: 0 auto; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        input[type=file] { margin: 20px 0; }
        button { background: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
        button:hover { background: #2980b9; }
        .guide { text-align: left; background: #eee; padding: 15px; border-radius: 5px; margin-top: 20px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="box">
        <h2>📂 Nạp Câu Hỏi Vào Hệ Thống</h2>
        <?php echo $message; ?>
        
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="file" accept=".txt" required>
            <br>
            <button type="submit" name="submit">Upload ngay</button>
        </form>

        <div class="guide">
            <b>Hướng dẫn tạo file .txt:</b><br>
            Mỗi dòng là 1 câu hỏi, ngăn cách bởi dấu gạch đứng (<b>|</b>)<br><br>
            <i>Ví dụ:</i><br>
            Thủ đô Việt Nam? | Hà Nội | TP.HCM | Đà Nẵng | Huế<br>
            5 + 5 = ? | 10 | 11 | 12 | 13
        </div>
        
        <br>
        <a href="index.php">Về trang chủ</a>
    </div>
</body>
</html>
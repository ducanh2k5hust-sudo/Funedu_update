<?php
session_start();
include 'db.php';

if (isset($_SESSION['user_id']) && isset($_POST['score'])) {
    $user_id = $_SESSION['user_id'];
    $score = intval($_POST['score']);
    
    
    $game_name = isset($_POST['game_name']) ? $_POST['game_name'] : 'Đếm hình đoán số';

    $stmt = $conn->prepare("INSERT INTO scores (user_id, score, game_name) VALUES (?, ?, ?)");

    $stmt->bind_param("iis", $user_id, $score, $game_name);
    
    if ($stmt->execute()) {
        echo "Success";
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    echo "Chua dang nhap";
}
?>

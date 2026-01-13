<?php 
session_start(); 
include 'db.php'; 

if (!isset($_SESSION['user_id']) && !isset($_SESSION['is_guest'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bé Tập So Sánh - Five Edu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;700&family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        /* --- CẤU HÌNH GIAO DIỆN --- */
        body { 
            margin: 0; 
            font-family: 'Nunito', sans-serif; 
            background: linear-gradient(135deg, #a8e063 0%, #56ab2f 100%);
            height: 100vh; 
            display: flex; 
            flex-direction: column; 
            overflow: hidden; 
            user-select: none;
        }
        
        /* HEADER */
        .header-ui { 
            padding: 15px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            background: rgba(255,255,255,0.2); 
            backdrop-filter: blur(5px);
            z-index: 10;
        }
        .btn-circle { 
            background: #fff; width: 45px; height: 45px; border-radius: 50%; 
            border: 3px solid #fff; color: #56ab2f; 
            display: flex; align-items: center; justify-content: center; 
            text-decoration: none; font-size: 20px; 
            box-shadow: 0 4px 0 rgba(0,0,0,0.1);
        }

        .stats { 
            font-size: 20px; font-weight: 900; color: #fff; 
            background: rgba(0,0,0,0.3); padding: 8px 20px; border-radius: 30px; 
            font-family: 'Comfortaa', cursive;
        }

        /* TRUNG TÂM GAME */
        .game-area { 
            flex: 1; display: flex; flex-direction: column; 
            justify-content: center; align-items: center; 
            gap: 20px; width: 100%;
        }
        
        .question-title {
            font-size: 30px; color: white; font-weight: 900;
            text-shadow: 2px 2px 0 rgba(0,0,0,0.2);
            font-family: 'Comfortaa', cursive;
            display: flex; align-items: center; gap: 10px;
        }

        .speaker-btn {
            background: #ff9f43; color: white; border: none;
            width: 50px; height: 50px; border-radius: 50%;
            font-size: 25px; cursor: pointer;
            box-shadow: 0 4px 0 #e67e22;
        }
        .speaker-btn:active { transform: translateY(3px); box-shadow: none; }

        
        .compare-container { 
            display: flex; align-items: center; gap: 20px; 
            transition: transform 0.3s;
        }
        
       
        .number-box { 
            width: 120px; height: 120px; 
            background: white; border-radius: 25px; 
            border-bottom: 8px solid #bdc3c7;
            display: flex; justify-content: center; align-items: center;
            font-size: 80px; font-weight: 900; color: #2C3E50;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .left-box { color: #2980b9; border: 5px solid #2980b9; border-bottom-width: 10px; }
        .right-box { color: #c0392b; border: 5px solid #c0392b; border-bottom-width: 10px; }
        
        .operator-placeholder {
            width: 70px; height: 70px;
            border-radius: 15px; background: rgba(0,0,0,0.2);
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 40px; font-weight: bold;
            border: 3px dashed rgba(255,255,255,0.5);
        }

        /* NÚT BẤM */
        .buttons-grid { display: flex; gap: 20px; margin-top: 20px; }
        .ans-btn { 
            width: 90px; height: 90px; 
            border: none; border-radius: 20px; 
            font-size: 50px; color: white; cursor: pointer;
            box-shadow: 0 8px 0 rgba(0,0,0,0.2);
            display: flex; align-items: center; justify-content: center;
        }
        .ans-btn:active { transform: translateY(6px); box-shadow: none; }
        
        .btn-less { background: #e74c3c; }    
        .btn-equal { background: #f1c40f; }   
        .btn-greater { background: #2ecc71; } 

        /* MÀN HÌNH BẮT ĐẦU (Fix lỗi mất số) */
        #startOverlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.85); z-index: 999;
            display: flex; flex-direction: column;
            justify-content: center; align-items: center;
        }
        .start-btn {
            background: #e74c3c; color: white; border: none;
            padding: 20px 40px; font-size: 30px; font-weight: 900;
            border-radius: 50px; cursor: pointer;
            animation: pulse 1s infinite;
            box-shadow: 0 0 20px #e74c3c;
        }

        /* MODAL KẾT QUẢ */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 100; justify-content: center; align-items: center; }
        .modal-content { background: white; padding: 30px; border-radius: 20px; text-align: center; border: 5px solid #f1c40f; width: 90%; max-width: 400px; }

        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.1); } 100% { transform: scale(1); } }
        @keyframes popIn { 0% { transform: scale(0); opacity:0; } 80% { transform: scale(1.1); opacity:1; } 100% { transform: scale(1); opacity:1; } }

        /* Responsive Mobile */
        @media (max-width: 600px) {
            .number-box { width: 100px; height: 100px; font-size: 50px; }
            .ans-btn { width: 75px; height: 75px; font-size: 35px; }
            .compare-container { gap: 10px; }
            .operator-placeholder { width: 50px; height: 50px; font-size: 30px; }
        }
    </style>
</head>
<body>

    <div id="startOverlay">
        <button class="start-btn" onclick="initGame()">▶️ CHƠI NGAY</button>
        <p style="color: white; margin-top: 20px;">Bấm để bắt đầu nha bé ơi!</p>
    </div>

    <div class="header-ui">
        <a href="index.php" class="btn-circle"><i class="fas fa-arrow-left"></i></a>
        <div class="stats" style="background: #e67e22;"><i class="fas fa-star"></i> <span id="score">0</span></div>
        <div class="stats" style="background: #2980b9;"><i class="fas fa-clock"></i> <span id="timer">60</span>s</div>
    </div>

    <div class="game-area">
        <div class="question-title">
            <span>Bé chọn dấu nào?</span>
            <button class="speaker-btn" onclick="speakCurrentQuestion()"><i class="fas fa-volume-up"></i></button>
        </div>
        
        <div class="compare-container" id="compareBox">
            <div class="number-box left-box" id="num1">?</div>
            <div class="operator-placeholder" id="operatorSlot"><i class="fas fa-question"></i></div>
            <div class="number-box right-box" id="num2">?</div>
        </div>

        <div class="buttons-grid">
            <button class="ans-btn btn-less" onclick="check('<')"><i class="fas fa-less-than"></i></button>
            <button class="ans-btn btn-equal" onclick="check('=')"><i class="fas fa-equals"></i></button>
            <button class="ans-btn btn-greater" onclick="check('>')"><i class="fas fa-greater-than"></i></button>
        </div>
    </div>

    <div class="modal" id="endModal">
        <div class="modal-content">
            <h1 style="color: #e67e22;">HẾT GIỜ RỒI!</h1>
            <h2 style="font-size: 40px;">+<span id="finalScore">0</span> điểm</h2>
            <button class="start-btn" style="font-size: 20px; padding: 15px 30px;" onclick="location.reload()">Chơi lại</button>
            <br><br>
            <a href="index.php" style="color:#555; font-weight:bold; text-decoration:none;">Về trang chủ</a>
        </div>
    </div>

    <script>
        let score = 0;
        let timeLeft = 60;
        let numA = 0, numB = 0;
        let timerInterval;
        let canPlay = false;
        
        const synthesis = window.speechSynthesis;

        function initGame() {
            // Ẩn màn hình chờ
            document.getElementById('startOverlay').style.display = 'none';
            // Bắt đầu tạo số
            generateNumbers();
            // Đếm giờ
            timerInterval = setInterval(() => {
                timeLeft--;
                document.getElementById('timer').innerText = timeLeft;
                if (timeLeft <= 0) endGame();
            }, 1000);
            
           
            speak(" Bé hãy chọn dấu thích hợp nhé.");
        }

        function speak(text) {
            if (synthesis.speaking) synthesis.cancel(); 

            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'vi-VN'; 
            utterance.rate = 0.7;  
            utterance.volume = 1.0; 
            utterance.pitch = 1.1; 
            
            synthesis.speak(utterance);
        }

        function speakCurrentQuestion() {
            // Đọc từng số rõ ràng
            let text = `Số ${numA} ... và số ${numB} ... điền dấu gì?`;
            if (numA == numB) text = `Số ${numA} ... và số ${numB} giống nhau ... điền dấu gì?`;
            speak(text);
        }

        function generateNumbers() {
            canPlay = true;
            // Random số từ 1 đến 10
            numA = Math.floor(Math.random() * 10) + 1;
            numB = Math.floor(Math.random() * 10) + 1;
            
            // Hiển thị ra màn hình
            document.getElementById('num1').innerText = numA;
            document.getElementById('num2').innerText = numB;
            
            // Reset ô dấu hỏi
            document.getElementById('operatorSlot').innerHTML = '<i class="fas fa-question"></i>';
            document.getElementById('operatorSlot').style.background = 'rgba(0,0,0,0.2)';

            // Hiệu ứng nảy ra (Reset animation)
            const box = document.getElementById('compareBox');
            box.style.animation = 'none';
            box.offsetHeight; /* trigger reflow */
            box.style.animation = 'popIn 0.5s forwards';

            // Đọc câu hỏi (Trễ 0.5s để hiệu ứng hiện ra xong mới đọc)
            setTimeout(speakCurrentQuestion, 300);
        }

        function check(operator) {
            if(!canPlay || timeLeft <= 0) return;

            let isCorrect = false;
            if (operator === '<' && numA < numB) isCorrect = true;
            else if (operator === '>' && numA > numB) isCorrect = true;
            else if (operator === '=' && numA === numB) isCorrect = true;

            if (isCorrect) {
                // ĐÚNG
                score += 10;
                document.getElementById('score').innerText = score;
                document.getElementById('operatorSlot').innerHTML = operator;
                document.getElementById('operatorSlot').style.background = '#2ecc71';
                
                canPlay = false; 
                
                // Đọc khen
                speak("Đúng rồi! ");
                
                setTimeout(generateNumbers, 1200); 
            } else {
                // SAI
                document.getElementById('operatorSlot').style.background = '#e74c3c';
                speak("Sai rồi! Bé chọn lại đi.");
            }
        }

        function endGame() {
            clearInterval(timerInterval);
            canPlay = false;
            document.getElementById('finalScore').innerText = score;
            document.getElementById('endModal').style.display = 'flex';
            speak("Hết giờ rồi. Bé chơi giỏi lắm.");

            // Lưu điểm
            let formData = new FormData(); 
            formData.append('score', score); 
            formData.append('game_name', 'So Sánh');
            fetch('luu-diem.php', { method: 'POST', body: formData });
        }
    </script>
</body>
</html>
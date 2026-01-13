<?php 
session_start(); 
// Bắt buộc đăng nhập để lưu điểm
if (!isset($_SESSION['user_id']) && !isset($_SESSION['is_guest'])) {
    header("Location: dangnhap.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vui Tết Học Toán - Five Edu</title>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/control_utils/control_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/hands/hands.js" crossorigin="anonymous"></script>
    
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&family=Comfortaa:wght@700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* --- THEME TẾT --- */
        :root {
            --tet-red: #d90429;
            --tet-gold: #ffc300;
            --tet-bg: linear-gradient(135deg, #9b2226 0%, #d90429 100%);
        }

        body {
            font-family: 'Comfortaa', cursive;
            background: var(--tet-bg);
            margin: 0; height: 100vh; display: flex; flex-direction: column; overflow: hidden;
            color: #fff;
        }

        .header {
            padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; z-index: 10;
            height: 60px;
        }
        .back-btn {
            background: #fff; padding: 10px 20px; border-radius: 25px; text-decoration: none;
            color: var(--tet-red); font-weight: 900; box-shadow: 0 4px 0 rgba(0,0,0,0.2); border: 2px solid var(--tet-gold);
            font-size: 16px; transition: 0.2s;
        }
        .back-btn:active { transform: translateY(3px); box-shadow: none; }

        .score-board {
            background: var(--tet-gold); color: var(--tet-red); padding: 8px 20px;
            border-radius: 20px; font-weight: 900; font-size: 18px; border: 2px solid #fff;
            box-shadow: 0 4px 0 rgba(0,0,0,0.2);
        }

        /* --- MENU START --- */
        #menu-screen {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(155, 34, 38, 0.95); z-index: 50;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
        }
        .menu-title { 
            font-size: 40px; color: var(--tet-gold); font-weight: 900; 
            margin-bottom: 40px; text-align: center; 
            text-shadow: 3px 3px 0 #5a0003;
        }
        
        .mode-btn {
            width: 300px; padding: 25px; margin: 15px; border-radius: 25px;
            border: 4px solid var(--tet-gold); cursor: pointer; position: relative;
            box-shadow: 0 10px 0 rgba(0,0,0,0.2); transition: 0.2s;
            display: flex; align-items: center; text-decoration: none; color: var(--tet-red);
            background: #fffdf0;
        }
        .mode-btn:active { transform: translateY(5px); box-shadow: none; }
        .mode-btn:hover { background: #fff; transform: scale(1.02); }

        .mode-icon { font-size: 45px; margin-right: 20px; width: 60px; text-align: center; color: var(--tet-red); }
        .mode-text { font-size: 24px; font-weight: 900; font-family: 'Comfortaa'; }
        .mode-desc { font-size: 14px; color: #555; font-weight: 700; display: block; margin-top: 5px;}

        /* --- GAME SCREEN --- */
        #game-screen {
            display: none; flex: 1; justify-content: center; align-items: center; 
            flex-direction: column; position: relative;
            padding-top: 50px; 
        }

        .game-container { 
            position: relative; 
            display: flex; 
            justify-content: center; 
            width: 100%; 
            height: 100%; 
            align-items: center;
        }

        /* CAMERA TO ĐÙNG */
        .output_canvas {
            border-radius: 30px; 
            border: 8px solid var(--tet-gold);
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            width: 90%; 
            height: auto;
            aspect-ratio: 4/3; 
            max-width: 1000px; 
            max-height: 75vh;
            background: #000; 
            transform: scaleX(-1); 
            object-fit: cover;
        }

        /* KHUNG CÂU HỎI */
        .question-box {
            position: absolute; 
            top: -40px; 
            left: 50%; 
            transform: translateX(-50%);
            background: #fffdf0; 
            padding: 10px 30px; 
            border-radius: 25px;
            text-align: center; 
            border: 5px solid var(--tet-gold); 
            box-shadow: 0 8px 0 #c0392b; 
            z-index: 5; 
            min-width: 300px;
        }
        .q-progress { font-size: 14px; color: #7f8c8d; font-weight: 800; margin-bottom: 5px; }
        .q-content { 
            font-size: 40px; color: var(--tet-red); font-weight: 900; 
            display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;
        }
        
        .count-item { font-size: 45px; animation: popIn 0.5s ease-out backwards; }

        /* HIỆU ỨNG ĐÚNG/SAI */
        .feedback-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            display: none; justify-content: center; align-items: center; flex-direction: column;
            z-index: 20; backdrop-filter: blur(5px);
            border-radius: 30px;
        }
        .fb-correct { background: rgba(39, 174, 96, 0.8); }
        .fb-wrong { background: rgba(192, 57, 43, 0.8); }

        .fb-text { font-size: 50px; color: #fff; font-weight: 900; text-shadow: 3px 3px 0 rgba(0,0,0,0.2); text-align: center; animation: popIn 0.3s; }

        /* ĐẾM NGÓN TAY */
        .finger-status {
            margin-top: 10px; margin-bottom: 10px;
            background: rgba(0,0,0,0.5); padding: 10px 30px; 
            border-radius: 20px; color: var(--tet-gold); font-weight: 900; font-size: 24px;
            border: 2px solid var(--tet-gold);
            z-index: 10;
        }

        /* MÀN HÌNH KẾT THÚC */
        #end-screen {
            display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.95); z-index: 60;
            flex-direction: column; align-items: center; justify-content: center;
        }

        @keyframes popIn { from { transform: scale(0); } to { transform: scale(1); } }
    </style>
</head>
<body>

    <div class="header">
        <a href="index.php" class="back-btn"><i class="fas fa-home"></i> Về Nhà</a>
        <div class="score-board">
            <i class="fas fa-star"></i> Điểm: <span id="scoreDisplay">0</span>
        </div>
    </div>

    <div id="menu-screen">
        <div class="menu-title">🧨 VUI TẾT HỌC TOÁN 🧨</div>
        
        <div class="mode-btn" onclick="startGame('count_obj')">
            <div class="mode-icon"><i class="fas fa-gift"></i></div>
            <div>
                <div class="mode-text">Đếm Lì Xì</div>
                <span class="mode-desc">Nhìn hình đếm số lượng</span>
            </div>
        </div>

        <div class="mode-btn" onclick="startGame('math')">
            <div class="mode-icon"><i class="fas fa-calculator"></i></div>
            <div>
                <div class="mode-text">Tính Lì Xì</div>
                <span class="mode-desc">Phép cộng trừ đơn giản</span>
            </div>
        </div>
    </div>

    <div id="game-screen">
        <div class="game-container">
            <div class="question-box">
                <div class="q-progress">Câu hỏi: <span id="qIndex">1</span>/10</div>
                <div class="q-content" id="qContent">...</div>
                <div id="qInstruction" style="font-size: 16px; color: #555; margin-top: 5px;">Bé hãy đếm xem?</div>
            </div>

            <video class="input_video" style="display:none"></video>
            <canvas class="output_canvas" width="1280" height="960"></canvas>
            
            <div class="feedback-overlay fb-correct" id="overlayCorrect">
                <div class="fb-text">ĐÚNG RỒI!<br>+10 Điểm</div>
                <div style="font-size: 80px;">😍</div>
            </div>

            <div class="feedback-overlay fb-wrong" id="overlayWrong">
                <div class="fb-text">SAI RỒI!<br>Tiếc quá</div>
                <div style="font-size: 30px; margin-top:10px; color:#fff">Đáp án là: <span id="correctAnswerDisplay"></span></div>
                <div style="font-size: 80px;">😭</div>
            </div>
        </div>
        
        <div class="finger-status">
            🖐️ Bé đang giơ: <span id="currentFinger" style="color: #fff; font-size: 30px;">0</span> ngón
        </div>
    </div>

    <div id="end-screen">
        <div style="font-size: 40px; color: var(--tet-gold); font-weight: 900; margin-bottom: 20px;">TỔNG KẾT</div>
        <div style="font-size: 80px; color: #fff; font-weight: 900; text-shadow: 4px 4px 0 var(--tet-red);" id="finalScore">0</div>
        <div style="color: #fff; margin-bottom: 30px; font-size: 20px;">điểm</div>
        
        <button class="mode-btn" onclick="location.reload()" style="width: 200px; justify-content: center;">
            <div class="mode-text">Chơi lại</div>
        </button>
        <a href="index.php" style="color: #fff; text-decoration: underline; margin-top: 20px; font-size: 18px;">Về trang chủ</a>
    </div>

    <script>
        // --- CẤU HÌNH GAME ---
        const MAX_QUESTIONS = 10;
        const SCORE_PER_Q = 10;
        const HOLD_TIME_CORRECT = 20; // Giữ 1s để chọn đúng
        const HOLD_TIME_WRONG = 60;   // Giữ 3s sai thì bị tính là sai luôn

        // Biến trạng thái
        let currentMode = ''; 
        let currentScore = 0;
        let questionCount = 0;
        let targetResult = 0;
        
        // Biến xử lý tay
        let holdCounter = 0;
        let wrongHoldCounter = 0;
        let isProcessing = false;
        let isGameRunning = false;
        let lastFingerCount = -1;

        // DOM Elements
        const qContent = document.getElementById('qContent');
        const qIndex = document.getElementById('qIndex');
        const qInstruction = document.getElementById('qInstruction');
        const scoreDisplay = document.getElementById('scoreDisplay');
        const currentFingerDisplay = document.getElementById('currentFinger');
        const overlayCorrect = document.getElementById('overlayCorrect');
        const overlayWrong = document.getElementById('overlayWrong');
        const correctAnswerDisplay = document.getElementById('correctAnswerDisplay');

        // Danh sách icon để đếm
        const countIcons = [
            '<i class="fas fa-gift" style="color:#e74c3c"></i>', 
            '<i class="fas fa-star" style="color:#f1c40f"></i>', 
            '<i class="fas fa-apple-alt" style="color:#d35400"></i>', 
            '<i class="fas fa-cookie" style="color:#8e44ad"></i>' 
        ];

        // --- HỆ THỐNG GAME ---
        function startGame(mode) {
            currentMode = mode;
            currentScore = 0;
            questionCount = 0;
            scoreDisplay.innerText = "0";
            
            document.getElementById('menu-screen').style.display = 'none';
            document.getElementById('game-screen').style.display = 'flex';
            
            if (!camera) startCamera();
            
            nextQuestion();
        }

        function nextQuestion() {
            // Reset trạng thái
            overlayCorrect.style.display = 'none';
            overlayWrong.style.display = 'none';
            isProcessing = false;
            holdCounter = 0;
            wrongHoldCounter = 0;
            lastFingerCount = -1; // Reset tay để tránh bị nhận diện nhầm ngay khi vào câu mới

            if (questionCount >= MAX_QUESTIONS) {
                endGame();
                return;
            }

            questionCount++;
            qIndex.innerText = questionCount;

            generateQuestionContent();
        }

        function generateQuestionContent() {
            if (currentMode === 'count_obj') {
                targetResult = Math.floor(Math.random() * 10) + 1; 
                let icon = countIcons[Math.floor(Math.random() * countIcons.length)];
                let html = "";
                for(let i=0; i<targetResult; i++) {
                    html += `<div class="count-item" style="animation-delay: ${i*0.1}s">${icon}</div>`;
                }
                qContent.innerHTML = html;
                qInstruction.innerText = "Có bao nhiêu hình ở trên?";
                speak("Bé hãy đếm xem có bao nhiêu hình?");

            } else if (currentMode === 'math') {
                let isPlus = Math.random() > 0.5;
                if (isPlus) {
                    let a = Math.floor(Math.random() * 5);
                    let b = Math.floor(Math.random() * (10 - a)); 
                    targetResult = a + b;
                    qContent.innerText = `${a} + ${b} = ?`;
                    qInstruction.innerText = "Phép cộng";
                    speak(`${a} cộng ${b} bằng mấy?`);
                } else {
                    let a = Math.floor(Math.random() * 10) + 1;
                    let b = Math.floor(Math.random() * a); 
                    targetResult = a - b;
                    qContent.innerText = `${a} - ${b} = ?`;
                    qInstruction.innerText = "Phép trừ";
                    speak(`${a} trừ ${b} bằng mấy?`);
                }
            }
        }

        // --- XỬ LÝ ĐÁP ÁN ---
        function checkAnswer(fingers) {
            if (isProcessing) return;

            // Nếu tay thay đổi số lượng -> Reset bộ đếm ổn định
            if (fingers !== lastFingerCount) {
                holdCounter = 0;
                wrongHoldCounter = 0;
                lastFingerCount = fingers;
                return;
            }

            // 1. TRƯỜNG HỢP ĐÚNG
            if (fingers === targetResult) {
                holdCounter++;
                wrongHoldCounter = 0; 
                
                if (holdCounter > HOLD_TIME_CORRECT) {
                    handleCorrect();
                }
            } 
            // 2. TRƯỜNG HỢP SAI (Giữ tay sai đủ lâu thì tính là chốt đáp án sai)
            else if (fingers > 0) {
                wrongHoldCounter++;
                holdCounter = 0; 
                
                if (wrongHoldCounter > HOLD_TIME_WRONG) {
                    handleWrong();
                }
            }
        }

        function handleCorrect() {
            isProcessing = true; // Khóa game lại
            currentScore += SCORE_PER_Q; // Cộng điểm
            scoreDisplay.innerText = currentScore;
            
            overlayCorrect.style.display = 'flex';
            speak("Đúng rồi! Giỏi quá!");
            
            setTimeout(nextQuestion, 2500); // Chuyển câu
        }

        function handleWrong() {
            isProcessing = true; // Khóa game lại
            
            // Hiện đáp án đúng lên màn hình
            correctAnswerDisplay.innerText = targetResult;
            overlayWrong.style.display = 'flex';
            
            speak("Sai mất rồi! Đáp án là " + targetResult);
            
            // QUAN TRỌNG: Không cộng điểm, đợi 3s rồi chuyển câu tiếp theo luôn
            setTimeout(nextQuestion, 3000); 
        }

        function endGame() {
            isGameRunning = false;
            document.getElementById('game-screen').style.display = 'none';
            document.getElementById('end-screen').style.display = 'flex';
            document.getElementById('finalScore').innerText = currentScore;

            speak("Chúc mừng bé đã hoàn thành! Bé được " + currentScore + " điểm.");

            // Lưu điểm vào Database
            saveScoreToDB();
        }

        function saveScoreToDB() {
            let gameName = (currentMode === 'count_obj') ? 'Đếm hình Tết' : 'Toán Tết AI';
            let formData = new FormData();
            formData.append('score', currentScore);
            formData.append('game_name', gameName); 
            
            fetch('luu-diem.php', { method: 'POST', body: formData })
            .then(response => response.text())
            .then(data => console.log("Đã lưu điểm: " + data));
        }

        function speak(text) {
            window.speechSynthesis.cancel();
            const msg = new SpeechSynthesisUtterance(text);
            msg.lang = 'vi-VN'; msg.rate = 0.9;
            window.speechSynthesis.speak(msg);
        }

        // --- MEDIAPIPE ---
        let hands, camera;
        const videoElement = document.querySelector('.input_video');
        const canvasElement = document.querySelector('.output_canvas');
        const canvasCtx = canvasElement.getContext('2d');

        function onResults(results) {
            canvasCtx.save();
            canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);
            canvasCtx.drawImage(results.image, 0, 0, canvasElement.width, canvasElement.height);

            let totalFingers = 0;
            if (results.multiHandLandmarks) {
                for (const landmarks of results.multiHandLandmarks) {
                    drawConnectors(canvasCtx, landmarks, HAND_CONNECTIONS, {color: '#ffff00', lineWidth: 4});
                    drawLandmarks(canvasCtx, landmarks, {color: '#ff0000', lineWidth: 2, radius: 4});
                    totalFingers += countFingersSingleHand(landmarks);
                }
            }
            
            currentFingerDisplay.innerText = totalFingers;
            checkAnswer(totalFingers);
            canvasCtx.restore();
        }

        function countFingersSingleHand(landmarks) {
            let count = 0;
            const getDist = (i, j) => Math.hypot(landmarks[i].x - landmarks[j].x, landmarks[i].y - landmarks[j].y);
            if (getDist(8, 0) > getDist(6, 0)) count++;
            if (getDist(12, 0) > getDist(10, 0)) count++;
            if (getDist(16, 0) > getDist(14, 0)) count++;
            if (getDist(20, 0) > getDist(18, 0)) count++;
            if (getDist(4, 17) > getDist(2, 17)) count++;
            return count;
        }

        function startCamera() {
            hands = new Hands({locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/hands/${file}`});
            hands.setOptions({ maxNumHands: 2, modelComplexity: 1, minDetectionConfidence: 0.7, minTrackingConfidence: 0.5 });
            hands.onResults(onResults);
            
            camera = new Camera(videoElement, {
                onFrame: async () => { await hands.send({image: videoElement}); },
                width: 1280, height: 960 // Độ phân giải cao
            });
            camera.start();
        }
    </script>
</body>
</html>
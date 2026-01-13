<?php 
session_start(); 
include 'db.php'; 

// --- PHP: LẤY CÂU HỎI ---
$db_questions = [];
if(isset($conn)) {
    $sql = "SELECT * FROM questions ORDER BY RAND() LIMIT 10";
    $result = $conn->query($sql);
    if($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $db_questions[] = $row;
        }
    }
}
$json_questions = json_encode($db_questions);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hiệp Sĩ Rồng - Giải Cứu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;700&family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        /* --- GIAO DIỆN SẠCH --- */
        body { 
            margin: 0; overflow: hidden; 
            font-family: 'Nunito', sans-serif; 
            background: linear-gradient(to bottom, #87CEEB 0%, #E0F7FA 100%);
            user-select: none;
        }
        
        /* UI HEADER */
        .header-ui { 
            position: absolute; top: 15px; left: 15px; right: 15px; 
            display: flex; justify-content: space-between; z-index: 100; 
        }
        
        .btn-circle { 
            background: white; width: 50px; height: 50px; border-radius: 50%; 
            border: 3px solid #3498db; color: #3498db; 
            display: flex; align-items: center; justify-content: center; 
            text-decoration: none; font-size: 22px; cursor: pointer; 
            box-shadow: 0 4px 0 rgba(0,0,0,0.1);
        }

        .stats-container { display: flex; gap: 10px; }
        .stats-box { 
            background: white; color: #333;
            padding: 8px 15px; border-radius: 20px; 
            border: 3px solid #bdc3c7; 
            font-weight: 900; display: flex; align-items: center; gap: 8px; 
            font-family: 'Comfortaa', cursive;
            box-shadow: 0 4px 0 rgba(0,0,0,0.1);
        }

        /* THẾ GIỚI GAME */
        .game-world { position: relative; width: 100%; height: 100vh; overflow: hidden; }
        
        /* Mặt đất */
        .ground { 
            position: absolute; bottom: 0; width: 100%; height: 140px; 
            background: #5D4037; border-top: 15px solid #4CAF50; 
            z-index: 5;
        }

        /* Cảnh vật */
        .scenery-layer {
            position: absolute; bottom: 140px; width: 100%; height: 300px;
            z-index: 1; pointer-events: none;
        }
        .mountain { position: absolute; bottom: 0; font-size: 250px; color: #90A4AE; opacity: 0.5; }
        .tree { position: absolute; bottom: -20px; font-size: 120px; color: #2E7D32; opacity: 0.9; }
        .cloud { position: absolute; top: 50px; color: rgba(255,255,255,0.8); font-size: 60px; animation: floatCloud 25s linear infinite; }

       
        .hero-img { 
            position: absolute; bottom: 130px; left: 15%; 
            width: 100px; height: 100px; 
            z-index: 20; 
            filter: drop-shadow(0 5px 5px rgba(0,0,0,0.3));
            transition: left 0.5s;
        }
        
        .enemy-img { 
            position: absolute; bottom: 130px; left: 120%; 
            width: 110px; height: 110px; 
            z-index: 20; 
            filter: drop-shadow(0 5px 5px rgba(0,0,0,0.3));
        }
        
        .castle-img {
            position: absolute; bottom: 120px; right: -350px;
            width: 250px; z-index: 10;
            display: none; 
        }

        /* OVERLAY START */
        #startOverlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(44, 62, 80, 0.95); z-index: 2000;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
        }
        .big-btn {
            background: #e74c3c; color: white; border: none; padding: 20px 60px;
            font-size: 30px; font-weight: 900; border-radius: 50px; cursor: pointer;
            box-shadow: 0 10px 0 #c0392b; animation: pulse 1s infinite;
        }

        /* MODAL CÂU HỎI */
        .question-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; justify-content: center; align-items: center; }
        .modal-content { 
            background: white; padding: 30px; border-radius: 20px; text-align: center; 
            border: 6px solid #3498db; width: 90%; max-width: 500px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.3); animation: popIn 0.3s;
        }
        .math-problem { 
            font-size: 45px; font-weight: 900; color: #2c3e50; 
            margin: 20px 0; background: #ecf0f1; padding: 15px; border-radius: 15px;
            display: flex; align-items: center; justify-content: center; gap: 15px;
        }
        .options-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 20px; }
        .ans-btn { 
            background: #3498db; color: white; border: none; padding: 20px; 
            font-size: 28px; font-weight: 900; border-radius: 15px; cursor: pointer; 
            box-shadow: 0 6px 0 #2980b9;
        }
        .ans-btn:active { transform: translateY(4px); box-shadow: 0 2px 0 #2980b9; }
        .speak-btn { width: 50px; height: 50px; border-radius: 50%; border: none; background: #f1c40f; color: white; font-size: 24px; cursor: pointer; box-shadow: 0 4px 0 #d35400; }

        @keyframes floatCloud { from { left: -100px; } to { left: 110%; } }
        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }
        @keyframes popIn { from { transform: scale(0); } to { transform: scale(1); } }

    </style>
</head>
<body>

    <div id="startOverlay">
        <h1 style="color:#f1c40f; font-size: 50px; margin-bottom: 10px; text-shadow: 3px 3px 0 #000;">🏰 GIẢI CỨU CÔNG CHÚA</h1>
        <p style="color:white; font-size: 20px; margin-bottom: 40px;">Trả lời đúng 10 câu để chiến thắng!</p>
        <button class="big-btn" onclick="startGame()">⚔️ LÊN ĐƯỜNG</button>
    </div>

    <div class="header-ui">
        <a href="index.php" class="btn-circle"><i class="fas fa-arrow-left"></i></a>
        <div class="stats-container">
            <div class="stats-box" style="border-color: #e74c3c; color: #e74c3c;">
                <i class="fas fa-heart"></i> <span id="lives">3</span>
            </div>
            <div class="stats-box" style="border-color: #f1c40f; color: #f39c12;">
                <i class="fas fa-dragon"></i> <span id="progress">0/10</span>
            </div>
            <div class="stats-box" style="border-color: #2ecc71; color: #27ae60;">
                <i class="fas fa-star"></i> <span id="score">0</span>
            </div>
        </div>
    </div>

    <div class="game-world" id="gameWorld">
        <i class="fas fa-cloud cloud" style="top: 10%;"></i>
        <i class="fas fa-cloud cloud" style="top: 25%; left: 40%; animation-duration: 30s; opacity: 0.7;"></i>
        <div class="scenery-layer">
            <i class="fas fa-mountain mountain" style="left: -50px;"></i>
            <i class="fas fa-mountain mountain" style="left: 20%; transform: scale(0.8);"></i>
            <i class="fas fa-mountain mountain" style="right: -100px;"></i>
            <i class="fas fa-tree tree" style="left: 10%; bottom: 0;"></i>
            <i class="fas fa-tree tree" style="left: 50%; bottom: 0; font-size: 80px;"></i>
            <i class="fas fa-tree tree" style="right: 20%; bottom: 0;"></i>
        </div>
        <div class="ground"></div>
        
        <img src="https://cdn-icons-png.flaticon.com/128/1492/1492436.png" class="hero-img" id="hero" alt="Hiệp sĩ">
        
        <img src="https://cdn-icons-png.flaticon.com/128/1236/1236413.png" class="enemy-img" id="enemy" alt="Quái vật">
        
        <img src="https://cdn-icons-png.flaticon.com/128/619/619097.png" class="castle-img" id="castle" alt="Lâu đài">
    </div>

    <div class="question-modal" id="qModal">
        <div class="modal-content">
            <h2 style="margin:0; color:#e67e22; font-family:'Comfortaa';">QUÁI VẬT CHẶN ĐƯỜNG!</h2>
            <div class="math-problem">
                <span id="questionText">...</span>
                <button class="speak-btn" onclick="speakCurrentQuestion()"><i class="fas fa-volume-up"></i></button>
            </div>
            <div class="options-grid" id="ansOptions"></div>
        </div>
    </div>

    <div class="question-modal" id="resultModal" style="z-index: 3000;">
        <div class="modal-content" style="border-color: #fff;">
            <div id="resultIcon" style="font-size: 80px; margin-bottom: 20px;"></div>
            <h1 id="resultTitle" style="margin:0;"></h1>
            <p id="resultMsg" style="font-size: 18px; color: #555;"></p>
            <h2 style="background: #eee; padding: 10px; border-radius: 10px;">Tổng điểm: <span id="finalScore">0</span></h2>
            <div style="display:flex; gap:10px; justify-content:center; margin-top:20px;">
                <button class="ans-btn" style="background: #95a5a6;" onclick="location.href='index.php'">Về nhà</button>
                <button class="ans-btn" style="background: #2ecc71;" onclick="location.reload()">Chơi lại</button>
            </div>
        </div>
    </div>

    <script>
        const dbQuestions = <?php echo $json_questions; ?>;
        const synthesis = window.speechSynthesis;

        let score = 0, lives = 3, progress = 0; 
        const target = 10;
        
        let isPaused = true;
        let enemyPos = 120; 
        
        // --- CHỈNH TỐC ĐỘ Ở ĐÂY ---
        let speed = 0.15; // Tốc độ khởi điểm (Rất chậm)
        let heroPos = 15; 
        
        // Danh sách ảnh quái vật (PNG)
        const monsters = [
            'https://cdn-icons-png.flaticon.com/128/8282/8282891.png',
            'https://cdn-icons-png.flaticon.com/128/1205/1205643.png', 
            'https://cdn-icons-png.flaticon.com/128/1236/1236448.png', 
            'https://cdn-icons-png.flaticon.com/128/3526/3526888.png' , 
        ];

        const hero = document.getElementById('hero');
        const enemy = document.getElementById('enemy');
        const qModal = document.getElementById('qModal');
        const resultModal = document.getElementById('resultModal');
        const qText = document.getElementById('questionText');
        
        function speak(text) {
            if (synthesis.speaking) synthesis.cancel();
            const u = new SpeechSynthesisUtterance(text);
            u.lang = 'vi-VN'; u.rate = 0.8; u.volume = 1;
            synthesis.speak(u);
        }

        function speakCurrentQuestion() {
            let text = qText.innerText;
            text = text.replace(/\+/g, ' cộng ').replace(/\-/g, ' trừ ').replace(/=/g, ' bằng ').replace(/\?/g, ''); 
            speak(text.trim());
        }

        function startGame() {
            document.getElementById('startOverlay').style.display = 'none';
            isPaused = false;
            speak("Lên đường thôi hiệp sĩ ơi!");
            requestAnimationFrame(gameLoop);
        }

        function gameLoop() {
            if(!isPaused) {
                enemyPos -= speed;
                enemy.style.left = enemyPos + '%';
                
                // Hiệu ứng nhún nhảy khi chạy
                let bounce = Math.sin(Date.now()/150) * 5;
                hero.style.bottom = (130 + bounce) + 'px';

                // Chạm trán
                if(enemyPos <= (heroPos + 10)) {
                    pauseGame();
                    showQuestion();
                }
            }
            requestAnimationFrame(gameLoop);
        }

        function pauseGame() { isPaused = true; }

        function resumeGame() {
            // Đánh bay quái vật
            enemy.style.transition = 'all 0.5s';
            enemy.style.transform = 'translate(100px, -300px) rotate(720deg) scale(0)';
            enemy.style.opacity = '0';
     

            setTimeout(() => {
                resetEnemy();
                isPaused = false;
            }, 600);
        }

        function resetEnemy() {
            enemyPos = 120;
            enemy.style.transition = 'none';
            enemy.style.transform = 'none';
            enemy.style.opacity = '1';
            
            // Random ảnh quái vật mới
            const m = monsters[Math.floor(Math.random() * monsters.length)];
            enemy.src = m;
            
            // Tăng tốc độ rất ít
            if(speed < 1.0) speed += 0.02;
        }

        function showQuestion() {
            qModal.style.display = 'flex';
            const grid = document.getElementById('ansOptions');
            grid.innerHTML = '';
            
            let qContent = "", currentResult = 0, answers = [];

            if (dbQuestions.length > 0 && Math.random() > 0.3) {
                const qData = dbQuestions[Math.floor(Math.random() * dbQuestions.length)];
                qContent = qData.question; currentResult = qData.correct;
                answers = [qData.correct, qData.wrong1, qData.wrong2, qData.wrong3];
            } else {
                const n1 = Math.floor(Math.random() * 10) + 1;
                const n2 = Math.floor(Math.random() * 10) + 1;
                if(Math.random() > 0.5) {
                    currentResult = n1 + n2; qContent = `${n1} + ${n2} = ?`;
                } else {
                    let max = Math.max(n1, n2), min = Math.min(n1, n2);
                    currentResult = max - min; qContent = `${max} - ${min} = ?`;
                }
                answers = [currentResult];
                while(answers.length < 4) {
                    let fake = Math.floor(Math.random() * 20);
                    if(!answers.includes(fake)) answers.push(fake);
                }
            }
            answers.sort(() => Math.random() - 0.5);
            qText.innerText = qContent;
            
            answers.forEach(ans => {
                let btn = document.createElement('button');
                btn.className = 'ans-btn';
                btn.innerText = ans;
                btn.onclick = () => checkAnswer(ans, currentResult);
                grid.appendChild(btn);
            });
            setTimeout(speakCurrentQuestion, 500);
        }

        function checkAnswer(ans, trueAns) {
            if(ans == trueAns) {
                score += 10; progress++;
                document.getElementById('score').innerText = score;
                document.getElementById('progress').innerText = progress + "/" + target;
                qModal.style.display = 'none';
                if(progress >= target) winGame(); else resumeGame();
            } else {
                lives--; document.getElementById('lives').innerText = lives;
                speak("Ái chà! Sai rồi.");
                qModal.firstElementChild.style.animation = 'none';
                qModal.firstElementChild.offsetHeight; 
                qModal.firstElementChild.style.animation = 'shake 0.5s'; // Cần thêm CSS shake nếu muốn
                if(lives <= 0) loseGame();
            }
        }

        function winGame() {
            document.getElementById('castle').style.display = 'block';
            document.getElementById('castle').style.right = '50px';
            document.getElementById('castle').style.transition = 'right 3s'; // Lâu đài trượt vào chậm
            
            hero.style.left = '60%'; hero.style.transition = 'left 3s'; // Hiệp sĩ chạy về lâu đài
            enemy.style.display = 'none'; qModal.style.display = 'none';

            speak("Thắng rồi! Công chúa ơi tớ đến đây!");
            setTimeout(() => { showResult(true, "CHIẾN THẮNG!", "Tuyệt vời ông mặt trời!"); }, 3000);
        }

        function loseGame() {
            qModal.style.display = 'none';
            showResult(false, "THUA CUỘC!", "Hết mạng rồi bé ơi.");
        }

        function showResult(isWin, title, msg) {
            resultModal.style.display = 'flex';
            document.getElementById('resultTitle').innerText = title;
            document.getElementById('resultMsg').innerText = msg;
            document.getElementById('finalScore').innerText = score;
            document.getElementById('resultTitle').style.color = isWin ? '#2ecc71' : '#e74c3c';
            document.getElementById('resultIcon').innerHTML = isWin ? '👑' : '😭';
            if(isWin) saveScore(score);
        }

        function saveScore(s) {
            let fd = new FormData(); fd.append('score', s); fd.append('game_name', 'Hiệp sĩ Rồng');
            fetch('luu-diem.php', { method: 'POST', body: fd });
        }
    </script>
</body>
</html>
<?php 
session_start(); 
include 'db.php'; // Kết nối Database để lấy câu hỏi

// --- PHP: LẤY 10 CÂU HỎI NGẪU NHIÊN TỪ DATABASE ---
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
// Chuyển dữ liệu PHP sang JSON để Javascript dùng
$json_questions = json_encode($db_questions);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giải Cứu Công Chúa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; overflow: hidden; font-family: 'Nunito', sans-serif; background: #87CEEB; }
        /* GIỮ NGUYÊN CSS CŨ */
        .header-ui { position: absolute; top: 10px; left: 10px; right: 10px; display: flex; justify-content: space-between; z-index: 100; }
        .stats-box { background: rgba(255,255,255,0.9); padding: 10px 20px; border-radius: 15px; border: 3px solid #2c3e50; font-weight: 900; display: flex; align-items: center; gap: 10px; }
        .btn-circle { background: #fff; padding: 10px; border-radius: 50%; border: 3px solid #2c3e50; color: #333; text-decoration: none; display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; cursor: pointer; transition: 0.2s; }
        .music-btn.playing { background: #2ecc71; color: white; border-color: #27ae60; animation: spin 4s linear infinite; }
        .game-world { position: relative; width: 100%; height: 100vh; overflow: hidden; border-bottom: 20px solid #5D4037; cursor: pointer; }
        .cloud { position: absolute; color: rgba(255,255,255,0.7); animation: float 30s linear infinite; }
        .castle { position: absolute; right: 20px; bottom: 120px; font-size: 150px; color: #95a5a6; z-index: 1; }
        .road { position: absolute; bottom: 0; width: 200%; height: 120px; background: repeating-linear-gradient(90deg, #27ae60 0, #27ae60 50px, #2ecc71 50px, #2ecc71 100px); animation: moveRoad 4s linear infinite; border-top: 5px solid #228B22; }
        .paused .road, .paused .cloud { animation-play-state: paused; }
        
        .hero { position: absolute; bottom: 100px; left: 10%; font-size: 80px; color: #3498db; z-index: 10; animation: bounce 0.8s infinite alternate; }
        .paused .hero { animation: none; }
        .enemy { position: absolute; bottom: 100px; left: 120%; font-size: 90px; z-index: 10; }
        .princess { position: absolute; bottom: 100px; left: 80%; font-size: 80px; color: #e84393; display: none; z-index: 5; animation: bounce 1s infinite alternate; }

        .question-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 999; justify-content: center; align-items: center; }
        .modal-content { background: white; padding: 30px; border-radius: 20px; text-align: center; border: 5px solid #f39c12; width: 90%; max-width: 500px; animation: popIn 0.3s; }
        .math-problem { font-size: 30px; font-weight: 900; color: #2c3e50; margin: 20px 0; line-height: 1.4; }
        .options-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .ans-btn { background: #3498db; color: white; border: none; padding: 15px; font-size: 20px; font-weight: bold; border-radius: 10px; cursor: pointer; border-bottom: 5px solid #2980b9; word-wrap: break-word; }
        .ans-btn:active { transform: translateY(4px); border-bottom: 0; }
        .click-hint { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-weight: 900; font-size: 24px; text-shadow: 2px 2px 0 #000; pointer-events: none; z-index: 50; animation: pulse 1s infinite; display: none; }
        @keyframes moveRoad { from { left: 0; } to { left: -100px; } }
        @keyframes bounce { from { transform: translateY(0); } to { transform: translateY(-10px); } }
        @keyframes popIn { from { transform: scale(0); } to { transform: scale(1); } }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        @keyframes pulse { 0% { opacity: 0.5; } 100% { opacity: 1; } }
        @keyframes attack { 0% { transform: translateX(0); } 50% { transform: translateX(50px); } 100% { transform: translateX(0); } }
    </style>
</head>
<body>
    <audio id="bgAudio" loop autoplay><source src="nhac-nen.mp3" type="audio/mpeg"></audio>

    <div class="header-ui">
        <div style="display:flex; gap:10px;">
            <a href="index.php" class="btn-circle"><i class="fas fa-home"></i></a>
            <div class="btn-circle music-btn" id="musicBtn" onclick="toggleMusic()"><i class="fas fa-music"></i></div>
        </div>
        <div class="stats-box" style="color: #2980b9;"><i class="fas fa-clock"></i> <span id="timer">01:30</span></div>
        <div class="stats-box" style="color: #e74c3c;"><i class="fas fa-heart"></i> <span id="lives">3</span></div>
        <div class="stats-box" style="color: #f39c12;">Điểm: <span id="score">0</span></div>
    </div>

    <div class="game-world" id="gameWorld">
        <div class="click-hint" id="clickHint">CHẠM ĐỂ BẮT ĐẦU 👆</div>
        <i class="fas fa-cloud cloud" style="top: 10%; font-size: 80px;"></i>
        <i class="fab fa-fort-awesome castle"></i>
        <div class="road"></div>
        <div class="hero" id="hero"><i class="fas fa-user-knight"></i></div>
        <div class="princess" id="princess"><i class="fas fa-user-crown"></i></div>
        <div class="enemy" id="enemy"><i class="fas fa-dragon" style="color: #c0392b;"></i></div>
    </div>

    <div class="question-modal" id="qModal">
        <div class="modal-content">
            <h2 style="margin:0; color:#e67e22;">TRẢ LỜI ĐỂ TẤN CÔNG!</h2>
            <div class="math-problem" id="questionText">...</div>
            <div class="options-grid" id="ansOptions"></div>
        </div>
    </div>

    <script>
        // --- NHẬN DỮ LIỆU TỪ PHP ---
        // Biến này chứa danh sách câu hỏi từ Database
        const dbQuestions = <?php echo $json_questions; ?>;
        
        const hero = document.getElementById('hero');
        const enemy = document.getElementById('enemy');
        const gameWorld = document.getElementById('gameWorld');
        const modal = document.getElementById('qModal');
        const audio = document.getElementById('bgAudio');
        const musicBtn = document.getElementById('musicBtn');
        const clickHint = document.getElementById('clickHint');
        const timerDisplay = document.getElementById('timer');
        
        window.addEventListener('load', function() {
            var promise = audio.play();
            if (promise !== undefined) { promise.then(_ => { musicBtn.classList.add('playing'); }).catch(error => { clickHint.style.display = 'block'; document.addEventListener('click', startMusicOnInteraction); }); }
        });
        function startMusicOnInteraction() { audio.play(); musicBtn.classList.add('playing'); clickHint.style.display = 'none'; document.removeEventListener('click', startMusicOnInteraction); }
        function toggleMusic() { if (audio.paused) { audio.play(); musicBtn.classList.add('playing'); } else { audio.pause(); musicBtn.classList.remove('playing'); } }

        const totalQuestions = 10;
        let questionCount = 0;
        let score = 0;
        let lives = 3;
        let isPaused = false;
        let enemyPos = 120; 
        let speed = 0.15; 
        let timeLeft = 90; 
        let timerInterval;

        const monsters = [ {icon: 'fa-dragon', color: '#c0392b'}, {icon: 'fa-spider', color: '#2c3e50'}, {icon: 'fa-ghost', color: '#8e44ad'} ];

        function startTimer() {
            timerInterval = setInterval(() => {
                if(timeLeft > 0) {
                    timeLeft--;
                    let m = Math.floor(timeLeft / 60);
                    let s = timeLeft % 60;
                    timerDisplay.innerText = (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s);
                    if(timeLeft < 10) timerDisplay.style.color = 'red';
                } else {
                    clearInterval(timerInterval);
                    gameOver("Hết giờ rồi!"); 
                }
            }, 1000);
        }

        function gameLoop() {
            if(!isPaused) {
                enemyPos -= speed;
                enemy.style.left = enemyPos + '%';
                if(enemyPos <= 18) { pauseGame(); showQuestion(); }
            }
            if(lives > 0 && questionCount < totalQuestions && timeLeft > 0) requestAnimationFrame(gameLoop);
        }

        function pauseGame() { isPaused = true; gameWorld.classList.add('paused'); }
        function resumeGame() {
            hero.style.animation = 'attack 0.5s'; 
            setTimeout(() => { hero.style.animation = 'bounce 0.8s infinite alternate'; resetEnemy(); isPaused = false; gameWorld.classList.remove('paused'); }, 500);
        }

        function resetEnemy() {
            enemyPos = 120;
            speed += 0.01;
            const randMonster = monsters[Math.floor(Math.random() * monsters.length)];
            enemy.innerHTML = `<i class="fas ${randMonster.icon}" style="color: ${randMonster.color}"></i>`;
        }

        let currentResult = "";
        
        function showQuestion() {
            modal.style.display = 'flex';
            const qText = document.getElementById('questionText');
            const ansGrid = document.getElementById('ansOptions');
            ansGrid.innerHTML = '';

            // --- KIỂM TRA: DÙNG CÂU HỎI DATABASE HAY TOÁN HỌC? ---
            if (dbQuestions.length > 0 && questionCount < dbQuestions.length) {
                // -> Có câu hỏi trong Database
                const qData = dbQuestions[questionCount]; // Lấy câu hỏi theo thứ tự
                qText.innerText = qData.question;
                currentResult = qData.correct;
                
                // Tạo mảng đáp án và trộn
                let answers = [qData.correct, qData.wrong1, qData.wrong2, qData.wrong3];
                answers.sort(() => Math.random() - 0.5); // Trộn đáp án
                
                answers.forEach(ans => {
                    let btn = document.createElement('button');
                    btn.className = 'ans-btn';
                    btn.innerText = ans;
                    btn.onclick = () => checkAnswer(ans);
                    ansGrid.appendChild(btn);
                });

            } else {
                // -> Không có câu hỏi (hoặc đã hết), dùng Toán học ngẫu nhiên
                const num1 = Math.floor(Math.random() * 10) + 1;
                const num2 = Math.floor(Math.random() * 10) + 1;
                currentResult = num1 + num2;
                qText.innerText = `${num1} + ${num2} = ?`;
                
                let answers = [currentResult];
                while(answers.length < 4) { let fake = Math.floor(Math.random() * 20); if(!answers.includes(fake)) answers.push(fake); }
                answers.sort(() => Math.random() - 0.5);
                
                answers.forEach(ans => {
                    let btn = document.createElement('button');
                    btn.className = 'ans-btn';
                    btn.innerText = ans;
                    btn.onclick = () => checkAnswer(ans);
                    ansGrid.appendChild(btn);
                });
            }
        }

        function checkAnswer(ans) {
            // So sánh (dùng == vì đáp án toán học là số, đáp án text là chuỗi)
            if(ans == currentResult) {
                modal.style.display = 'none'; score += 10; questionCount++; document.getElementById('score').innerText = score;
                if(questionCount >= totalQuestions) gameWin(); else resumeGame();
            } else {
                lives--; document.getElementById('lives').innerText = lives; modal.firstElementChild.style.borderColor = 'red'; setTimeout(() => modal.firstElementChild.style.borderColor = '#f39c12', 500);
                if(lives <= 0) gameOver("Hiệp sĩ đã kiệt sức!");
            }
        }

        function gameWin() { 
            clearInterval(timerInterval); enemy.style.display = 'none'; document.getElementById('princess').style.display = 'block'; hero.style.left = '70%'; hero.style.transition = 'left 2s'; setTimeout(() => { showResultModal("CHIẾN THẮNG! 🏰", "Bạn đã cứu được công chúa!", "#2ecc71"); }, 2000); 
        }
        
        function gameOver(reason) { 
            clearInterval(timerInterval); showResultModal("THUA CUỘC! 😭", reason, "#e74c3c"); 
        }

        function showResultModal(title, msg, color) {
            modal.style.display = 'flex';
            modal.innerHTML = `
                <div class="modal-content" style="border-color: ${color};">
                    <h1 style="color:${color};">${title}</h1>
                    <p>${msg}</p>
                    <h2 style="font-size:30px;">Tổng điểm: ${score}</h2>
                    <p id="saveStatus" style="font-size:12px; color:#999;">Đang lưu điểm...</p>
                    <button class="ans-btn" onclick="location.reload()" style="background:${color};">Chơi lại</button>
                    <a href="index.php" style="display:block; margin-top:15px; color:#555; font-weight:bold;">Về trang chủ</a>
                </div>
            `;
            saveScoreToServer(score);
        }

        function saveScoreToServer(finalScore) {
            let formData = new FormData(); formData.append('score', finalScore); formData.append('game_name', 'Giải cứu công chúa');
            fetch('luu-diem.php', { method: 'POST', body: formData }).then(r => r.text()).then(data => {
                const status = document.getElementById('saveStatus');
                if(data.trim() === "Success") { status.innerText = "✅ Đã lưu vào bảng thành tích!"; status.style.color = "green"; } else { status.innerText = "⚠️ Chưa lưu được (Có thể do chưa đăng nhập)"; }
            });
        }

        gameLoop();
        startTimer();
    </script>
</body>
</html>
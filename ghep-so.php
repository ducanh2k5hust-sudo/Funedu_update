<?php session_start(); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game: Đếm hình (10 Câu)</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    <script src="https://bernardo-castilho.github.io/DragDropTouch/DragDropTouch.js"></script>

    <style>
        :root { --primary-bg: #eef1f5; --dark-border: #2c3e50; --success-color: #20bf6b; --wrong-color: #eb4d4b; }
        * { box-sizing: border-box; }
        body { font-family: 'Nunito', sans-serif; background-color: var(--primary-bg); margin: 0; padding: 15px; text-align: center; color: #333; min-height: 100vh; display: flex; flex-direction: column; overscroll-behavior: none; touch-action: none; }
        
        .header-stats { display: flex; justify-content: space-between; align-items: center; background: white; padding: 10px 15px; border-radius: 15px; border: 3px solid var(--dark-border); margin-bottom: 20px; box-shadow: 4px 4px 0 rgba(0,0,0,0.1); }
        .stat-item { font-weight: 800; font-size: 16px; }
        .stat-label { font-size: 12px; color: #777; display: block; }
        .back-btn { text-decoration: none; color: #333; font-weight: 800; background: #dfe6e9; padding: 5px 10px; border: 2px solid var(--dark-border); border-radius: 8px; font-size: 14px; }
        
        .game-area { flex: 1; display: flex; flex-direction: column; align-items: center; width: 100%; max-width: 600px; margin: 0 auto; }
        .image-box { background: white; width: 100%; min-height: 220px; padding: 20px; border-radius: 25px; border: 4px dashed #bdc3c7; display: flex; align-items: center; justify-content: center; flex-wrap: wrap; gap: 15px; margin-bottom: 30px; transition: 0.3s; }
        .image-box.correct { border: 4px solid var(--success-color); background: #dff9fb; border-style: solid; }
        .image-box.wrong { border-color: var(--wrong-color); animation: shake 0.4s; }
        .game-icon { font-size: 45px; animation: popIn 0.5s; }
        
        .numbers-container { display: flex; justify-content: center; gap: 15px; flex-wrap: wrap; width: 100%; }
        .draggable-number { width: 75px; height: 75px; background: white; border: 3px solid var(--dark-border); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 35px; font-weight: 900; color: #2d3436; cursor: grab; box-shadow: 4px 4px 0 rgba(0,0,0,0.1); user-select: none; z-index: 100; transition: 0.2s; }
        .draggable-number:active { transform: scale(1.1); background: #ffeaa7; }

        .win-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.9); z-index: 999; flex-direction: column; align-items: center; justify-content: center; }
        .win-box { background: white; padding: 30px; border-radius: 20px; border: 5px solid var(--dark-border); text-align: center; width: 90%; max-width: 400px; }
        .result-row { display: flex; justify-content: space-between; margin: 10px 0; border-bottom: 1px solid #eee; padding-bottom: 5px; font-weight: bold; }

        @keyframes popIn { from { transform: scale(0); } to { transform: scale(1); } }
        @keyframes shake { 0%, 100% { transform: translateX(0); } 25%, 75% { transform: translateX(-10px); } 50% { transform: translateX(10px); } }
    </style>
</head>
<body>

    <div class="header-stats">
        <a href="index.php" class="back-btn"><i class="fas fa-home"></i></a>
        <div class="stat-item" style="color: #20bf6b;">
            <span class="stat-label">Đúng</span><span id="correctCount">0</span>
        </div>
        <div class="stat-item" style="color: #eb4d4b;">
            <span class="stat-label">Sai</span><span id="wrongCount">0</span>
        </div>
        <div class="stat-item" style="color: #d35400;">
            <span class="stat-label">Điểm</span><span id="score">0</span>
        </div>
    </div>
    <div style="text-align:right; font-weight:bold; color:#555; margin-bottom:20px;">Câu: <span id="currentQ">1</span>/10</div>

    <div class="game-area">
        <h2 style="margin: 0 0 10px 0; font-size: 18px; color: #555;">Kéo số vào hình nhé!</h2>
        <div class="image-box" id="dropZone"></div>
        <div class="numbers-container" id="numbersContainer"></div>
    </div>

    <div class="win-overlay" id="winOverlay">
        <div class="win-box">
            <h1 style="color: #20bf6b;">TỔNG KẾT 🎉</h1>
            <div class="result-row"><span>Số câu đúng:</span><span id="endCorrect" style="color: #20bf6b;">0</span></div>
            <div class="result-row"><span>Số câu sai:</span><span id="endWrong" style="color: #eb4d4b;">0</span></div>
            <div class="result-row" style="font-size: 24px; border: none; margin-top: 15px;"><span>Tổng điểm:</span><span id="endScore" style="color: #d35400;">0</span></div>
            
            <p id="saveStatus" style="font-size: 12px; color: #888; font-style: italic;">Đang lưu điểm...</p>
            
            <button onclick="location.reload()" style="margin-top: 20px; padding: 12px 30px; background: #3498db; color: white; border: 3px solid #2980b9; border-radius: 10px; font-weight: bold; cursor: pointer;">Chơi lại</button>
            <a href="index.php" style="display:block; margin-top:15px; color:#555; text-decoration:none; font-weight:bold;">Về trang chủ</a>
        </div>
    </div>

    <script>
        const totalQuestions = 10;
        const pointsPerCorrect = 10;
        const themes = [ {icon:'fa-apple-alt', color:'#e74c3c'}, {icon:'fa-car', color:'#3498db'}, {icon:'fa-star', color:'gold'}, {icon:'fa-fish', color:'#e67e22'}, {icon:'fa-ice-cream', color:'#9b59b6'}, {icon:'fa-dog', color:'#795548'}, {icon:'fa-cat', color:'#607d8b'}, {icon:'fa-heart', color:'#e91e63'}, {icon:'fa-futbol', color:'#2c3e50'} ];

        let currentQIndex = 1;
        let score = 0;
        let correctCount = 0;
        let wrongCount = 0;
        let currentTarget = 0;

        const dropZone = document.getElementById('dropZone');
        const numbersContainer = document.getElementById('numbersContainer');

        function generateQuestion() {
            document.getElementById('currentQ').innerText = currentQIndex;
            currentTarget = Math.floor(Math.random() * 9) + 1;
            const theme = themes[Math.floor(Math.random() * themes.length)];
            dropZone.className = 'image-box';
            dropZone.innerHTML = '';
            for(let i=0; i<currentTarget; i++) { dropZone.innerHTML += `<i class="fas ${theme.icon} game-icon" style="color: ${theme.color}"></i>`; }

            let options = [currentTarget];
            while(options.length < 3) { let r = Math.floor(Math.random() * 9) + 1; if(!options.includes(r)) options.push(r); }
            options.sort(() => Math.random() - 0.5);

            numbersContainer.innerHTML = '';
            options.forEach(num => {
                const el = document.createElement('div');
                el.className = 'draggable-number';
                el.draggable = true;
                el.innerText = num;
                el.dataset.value = num;
                el.addEventListener('dragstart', () => el.classList.add('dragging'));
                el.addEventListener('dragend', () => el.classList.remove('dragging'));
                numbersContainer.appendChild(el);
            });
        }

        dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
        dropZone.addEventListener('drop', e => {
            e.preventDefault(); dropZone.classList.remove('drag-over');
            if(dropZone.classList.contains('correct')) return;
            const draggingEl = document.querySelector('.dragging');
            if(!draggingEl) return;
            if(parseInt(draggingEl.dataset.value) === currentTarget) handleCorrect(draggingEl);
            else handleWrong();
        });

        function handleCorrect(el) {
            playSound(true);
            dropZone.classList.add('correct');
            dropZone.innerHTML = `<span style="font-size: 80px; font-weight: 900; color: #20bf6b; animation: popIn 0.5s;">${currentTarget}</span>`;
            el.style.visibility = 'hidden';
            score += pointsPerCorrect; correctCount++; updateStats();
            setTimeout(() => { if(currentQIndex < totalQuestions) { currentQIndex++; generateQuestion(); } else { endGame(); } }, 1200);
        }

        function handleWrong() {
            playSound(false); dropZone.classList.add('wrong');
            if(navigator.vibrate) navigator.vibrate(200);
            wrongCount++; updateStats();
            setTimeout(() => dropZone.classList.remove('wrong'), 500);
        }

        function updateStats() {
            document.getElementById('score').innerText = score;
            document.getElementById('correctCount').innerText = correctCount;
            document.getElementById('wrongCount').innerText = wrongCount;
        }

        function endGame() {
            document.getElementById('endCorrect').innerText = correctCount;
            document.getElementById('endWrong').innerText = wrongCount;
            document.getElementById('endScore').innerText = score;
            document.getElementById('winOverlay').style.display = 'flex';
            playSound(true);
            saveScoreToServer(score);
        }

        function saveScoreToServer(finalScore) {
            const statusText = document.getElementById('saveStatus');
            
            // Dùng FormData để gửi dữ liệu dạng POST
            let formData = new FormData();
            formData.append('score', finalScore);

            fetch('luu-diem.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if(data.trim() === "Success") {
                    statusText.innerText = "✅ Đã lưu điểm vào bảng thành tích!";
                    statusText.style.color = "green";
                } else if (data.trim() === "Chua dang nhap") {
                    statusText.innerText = "⚠️ Bạn chưa đăng nhập nên không lưu được điểm.";
                    statusText.style.color = "orange";
                } else {
                    statusText.innerText = "❌ Lỗi khi lưu điểm.";
                }
            })
            .catch(error => {
                statusText.innerText = "❌ Lỗi mạng.";
            });
        }

        function playSound(isCorrect) {
            try {
                const url = isCorrect ? 'https://actions.google.com/sounds/v1/cartoon/pop.ogg' : 'https://actions.google.com/sounds/v1/cartoon/clang_and_wobble.ogg';
                new Audio(url).play().catch(e=>{});
            } catch(e){}
        }

        generateQuestion();
    </script>
</body>
</html>
<?php
// File: game-duong-dua-rong.php
// Phiên bản: Có hướng dẫn + Nút điều khiển cố định dễ bấm
session_start();
include 'db.php'; //

if (!isset($_SESSION['user_id'])) {
    header("Location: dangnhap.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đường Đua Rồng Thiêng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Comfortaa', cursive;
            background: #fffdf0; margin: 0; padding: 0;
            height: 100vh; display: flex; flex-direction: column; overflow: hidden;
        }

        /* HEADER */
        .header-bar {
            background: #d90429; color: #ffc300;
            padding: 10px 20px; display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 4px 0 #9b2226; z-index: 10;
        }
        .back-btn { color: white; text-decoration: none; font-weight: 900; font-size: 16px; display: flex; align-items: center; gap: 5px; }

        /* BỐ CỤC CHÍNH (Chia đôi màn hình) */
        .main-layout {
            display: flex; flex: 1; overflow: hidden;
        }

        /* KHU VỰC BÀN CỜ (Trái) */
        .board-area {
            flex: 2; display: flex; align-items: center; justify-content: center;
            background: #fffdf0; padding: 20px; overflow-y: auto;
        }
        .board {
            display: grid; grid-template-columns: repeat(5, 1fr);
            gap: 5px; background: #ffc300; padding: 8px;
            border-radius: 15px; border: 4px solid #d90429;
            box-shadow: 8px 8px 0 rgba(0,0,0,0.2);
            width: 100%; max-width: 450px; /* Giới hạn chiều rộng để không bị to quá */
        }
        .cell {
            background: white; aspect-ratio: 1; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 900; color: #d90429; position: relative;
            border: 2px solid #f1c40f; font-size: 14px;
        }
        .cell.start { background: #2ecc71; color: white; }
        .cell.finish { background: #e74c3c; color: white; }
        .cell.ladder { background: #e3f2fd; border-color: #3498db; }
        .cell.snake { background: #fff3e0; border-color: #e67e22; }

        /* KHU VỰC ĐIỀU KHIỂN (Phải) */
        .control-panel {
            flex: 1; max-width: 350px; background: white;
            border-left: 4px solid #ffc300;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 20px; z-index: 5;
            box-shadow: -5px 0 15px rgba(0,0,0,0.05);
        }

        .dice-btn {
            width: 100px; height: 100px;
            background: #d90429; color: white;
            border-radius: 20px; border: 4px solid #ffc300;
            font-size: 50px; display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: 0.2s;
            box-shadow: 0 8px 0 #9b2226; margin-bottom: 20px;
        }
        .dice-btn:active { transform: translateY(5px); box-shadow: none; }
        .dice-btn.disabled { opacity: 0.5; pointer-events: none; background: #ccc; box-shadow: none; }
        
        .turn-info { font-size: 18px; font-weight: bold; color: #2c3e50; margin-bottom: 10px; text-align: center; }
        .log-box {
            width: 90%; height: 150px; background: #f8f9fa;
            border: 2px solid #ddd; border-radius: 10px;
            padding: 10px; overflow-y: auto; font-size: 13px; color: #555;
            text-align: left;
        }
        .log-item { margin-bottom: 5px; border-bottom: 1px dashed #eee; padding-bottom: 3px; }

        /* NHÂN VẬT */
        .player-piece {
            position: absolute; font-size: 24px; z-index: 10;
            filter: drop-shadow(2px 2px 0 rgba(0,0,0,0.3));
            transition: all 0.5s ease;
        }

        /* MODAL (Hộp thoại) */
        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.85); z-index: 100;
            display: flex; align-items: center; justify-content: center;
        }
        .modal-box {
            background: white; width: 90%; max-width: 400px;
            padding: 25px; border-radius: 20px; text-align: center;
            border: 5px solid #ffc300; position: relative;
            animation: popIn 0.3s;
        }
        @keyframes popIn { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        .btn-start {
            background: #27ae60; color: white; padding: 12px 30px;
            font-size: 20px; font-weight: bold; border: none; border-radius: 50px;
            cursor: pointer; margin-top: 15px; box-shadow: 0 5px 0 #1e8449;
            width: 100%;
        }
        .btn-start:hover { transform: translateY(-2px); }
        .btn-start:active { transform: translateY(3px); box-shadow: none; }

        .math-options { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-top: 20px; }
        .math-opt {
            background: #34495e; color: white; padding: 15px;
            border-radius: 10px; cursor: pointer; font-weight: bold; font-size: 20px;
        }
        .math-opt:hover { background: #d90429; }

        /* Responsive cho điện thoại */
        @media (max-width: 768px) {
            .main-layout { flex-direction: column; }
            .board-area { flex: 1; padding: 10px; }
            .board { max-width: 100%; gap: 3px; } /* Nhỏ hơn trên mobile */
            .control-panel { 
                flex: none; width: 100%; height: auto; max-width: none;
                flex-direction: row; justify-content: space-between; padding: 10px;
                border-left: none; border-top: 4px solid #ffc300;
            }
            .dice-btn { width: 60px; height: 60px; font-size: 30px; margin-bottom: 0; margin-right: 15px; }
            .log-box { height: 60px; flex: 1; font-size: 11px; }
            .turn-info { display: none; } /* Ẩn bớt chữ trên mobile */
        }
    </style>
</head>
<body>

    <div class="modal-overlay" id="introModal">
        <div class="modal-box">
            <h2 style="color:#d90429; margin-top:0;">🐉 ĐƯỜNG ĐUA RỒNG 🐉</h2>
            <div style="text-align: left; font-size: 15px; line-height: 1.6; color:#333;">
                <b>Nhiệm vụ:</b> Giúp Hiệp sĩ Rồng về đích số <b>30</b>.<br><br>
                🎲 <b>Cách chơi:</b><br>
                1. Bấm nút <b>Xúc Xắc</b> màu đỏ.<br>
                2. Trả lời đúng câu hỏi Toán để được đi.<br>
                3. Gặp 🚀 (Thang) sẽ bay lên cao.<br>
                4. Gặp 💣 (Rắn) sẽ bị tụt xuống.<br>
            </div>
            <button class="btn-start" onclick="closeIntro()">🎮 CHƠI NGAY</button>
        </div>
    </div>

    <div class="modal-overlay" id="mathModal" style="display: none;">
        <div class="modal-box">
            <div style="font-size: 16px; color: #7f8c8d;">Bé cần tính đúng để đi tiếp!</div>
            <h2 style="font-size: 35px; color: #2c3e50; margin: 15px 0;" id="mathQuestion">1 + 1 = ?</h2>
            <div class="math-options" id="mathAnswers"></div>
        </div>
    </div>

    <div class="header-bar">
        <a href="index.php" class="back-btn"><i class="fas fa-chevron-left"></i> Về Nhà</a>
        <span style="font-weight: 900;">🏆 VỀ ĐÍCH 30 🏆</span>
        <span></span>
    </div>

    <div class="main-layout">
        <div class="board-area">
            <div class="board" id="gameBoard"></div>
        </div>

        <div class="control-panel">
            <div class="turn-info">Lượt của Bé</div>
            
            <div class="dice-btn" id="diceBtn" onclick="rollDice()">
                <i class="fas fa-dice"></i>
            </div>
            
            <div class="log-box" id="gameLog">
                <div class="log-item">Chào mừng bé tham gia!</div>
                <div class="log-item">Hãy bấm xúc xắc để bắt đầu.</div>
            </div>
        </div>
    </div>

    <script>
        // --- CẤU HÌNH ---
        const totalCells = 30;
        const shortcuts = {
            3: { dest: 10, type: 'ladder', icon: 'fa-rocket' },
            6: { dest: 14, type: 'ladder', icon: 'fa-dragon' },
            18: { dest: 5, type: 'snake', icon: 'fa-bomb' },
            24: { dest: 12, type: 'snake', icon: 'fa-poop' }
        };
        let currentPos = 1;
        let isRolling = false;

        // --- HÀM KHỞI TẠO ---
        function closeIntro() {
            document.getElementById('introModal').style.display = 'none';
        }

        function initBoard() {
            const boardEl = document.getElementById('gameBoard');
            boardEl.innerHTML = '';
            for (let i = totalCells; i >= 1; i--) {
                const cell = document.createElement('div');
                cell.className = 'cell';
                cell.id = 'cell-' + i;
                cell.innerText = i;
                
                if (i === 1) { cell.innerText = 'Start'; cell.classList.add('start'); }
                if (i === totalCells) { cell.innerText = 'Đích'; cell.classList.add('finish'); }
                
                if (shortcuts[i]) {
                    const color = shortcuts[i].type === 'ladder' ? '#2980b9' : '#c0392b';
                    cell.classList.add(shortcuts[i].type);
                    cell.innerHTML += `<i class="fas ${shortcuts[i].icon}" style="position:absolute; top:2px; right:2px; color:${color}; font-size:12px;"></i>`;
                }
                boardEl.appendChild(cell);
            }
            renderPlayer(1);
        }

        function renderPlayer(pos) {
            document.querySelectorAll('.player-piece').forEach(el => el.remove());
            const cell = document.getElementById('cell-' + pos);
            if (cell) {
                const piece = document.createElement('div');
                piece.className = 'player-piece';
                piece.innerHTML = '<i class="fas fa-chess-knight" style="color:#d90429; font-size:28px;"></i>';
                cell.appendChild(piece);
            }
        }

        function log(msg) {
            const box = document.getElementById('gameLog');
            box.innerHTML = `<div class="log-item">${msg}</div>` + box.innerHTML;
        }

        // --- XỬ LÝ GAME ---
        function rollDice() {
            if (isRolling) return;
            isRolling = true;
            const btn = document.getElementById('diceBtn');
            btn.classList.add('disabled');
            
            // Hiệu ứng xoay
            let count = 0;
            const interval = setInterval(() => {
                const icons = ['dice-one', 'dice-two', 'dice-three', 'dice-four', 'dice-five', 'dice-six'];
                btn.innerHTML = `<i class="fas fa-${icons[Math.floor(Math.random()*6)]}"></i>`;
                count++;
                if(count > 10) {
                    clearInterval(interval);
                    const val = Math.floor(Math.random() * 6) + 1;
                    btn.innerHTML = val; // Hiện số
                    log(`🎲 Gieo được: ${val}`);
                    showMath(currentPos, val);
                }
            }, 50);
        }

        function showMath(start, move) {
            const correct = start + move;
            
            if (correct > totalCells) {
                log("🚫 Đi quá đích rồi! Bé chờ lượt sau.");
                resetTurn();
                return;
            }

            // Hiện bảng câu hỏi
            document.getElementById('mathModal').style.display = 'flex';
            document.getElementById('mathQuestion').innerText = `${start} + ${move} = ?`;
            
            // Tạo đáp án
            let answers = [correct];
            while(answers.length < 3) {
                let w = correct + Math.floor(Math.random() * 5) - 2;
                if(w > 0 && !answers.includes(w)) answers.push(w);
            }
            answers.sort(() => Math.random() - 0.5);

            const ansDiv = document.getElementById('mathAnswers');
            ansDiv.innerHTML = '';
            answers.forEach(a => {
                const btn = document.createElement('div');
                btn.className = 'math-opt';
                btn.innerText = a;
                btn.onclick = () => checkMath(a, correct, move);
                ansDiv.appendChild(btn);
            });
        }

        function checkMath(chosen, correct, move) {
            document.getElementById('mathModal').style.display = 'none';
            if (chosen === correct) {
                log("✅ Đúng rồi! Đang di chuyển...");
                movePlayer(move);
            } else {
                log(`❌ Sai rồi! ${currentPos} + ${move} = ${correct} cơ.`);
                resetTurn();
            }
        }

        function movePlayer(steps) {
            let next = currentPos + steps;
            currentPos = next;
            renderPlayer(currentPos);
            
            // Kiểm tra rắn/thang sau 0.5s
            setTimeout(() => {
                if (shortcuts[currentPos]) {
                    const s = shortcuts[currentPos];
                    currentPos = s.dest;
                    renderPlayer(currentPos);
                    log(s.type === 'ladder' ? "🚀 Bay lên nào!" : "💣 Ôi không! Tụt xuống rồi.");
                }
                
                if (currentPos === totalCells) {
                    alert("CHÚC MỪNG! BÉ ĐÃ VỀ ĐÍCH VÀ NHẬN 100 ĐIỂM!");
                    saveScore(100);
                    currentPos = 1;
                    renderPlayer(1);
                }
                resetTurn();
            }, 800);
        }

        function resetTurn() {
            isRolling = false;
            document.getElementById('diceBtn').classList.remove('disabled');
            document.getElementById('diceBtn').innerHTML = '<i class="fas fa-dice"></i>';
        }

        function saveScore(score) {
            // Gửi điểm về server
            const fd = new FormData();
            fd.append('score', score);
            fd.append('game_name', 'Đường Đua Rồng');
            fetch('luu-diem.php', { method: 'POST', body: fd });
        }

        // Chạy game
        initBoard();
    </script>
</body>
</html>
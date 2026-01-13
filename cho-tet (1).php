<?php
session_start();
include 'db.php';

// Chặn nếu chưa đăng nhập
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
    <title>Đi Chợ Tết - Five Edu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Nunito', sans-serif; margin: 0; overflow: hidden; 
            background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 99%, #fad0c4 100%);
            display: flex; flex-direction: column; height: 100vh; user-select: none;
        }

        /* HEADER */
        .header-ui {
            padding: 10px 20px; display: flex; justify-content: space-between; align-items: center;
            background: #fff; border-bottom: 4px solid #ff6b6b; z-index: 10;
        }
        .btn-home { font-size: 18px; color: #ff6b6b; text-decoration: none; border: 2px solid #ff6b6b; padding: 5px 15px; border-radius: 20px; font-weight: bold; }
        .level-badge { font-size: 20px; font-weight: 900; color: #fff; background: #e74c3c; padding: 5px 15px; border-radius: 20px; border: 2px solid white; box-shadow: 0 3px 0 rgba(0,0,0,0.2); }
        .score-box { font-size: 20px; font-weight: 900; color: #d35400; background: #ffeaa7; padding: 5px 15px; border-radius: 20px; }

        /* GAME AREA */
        .game-area { flex: 1; display: flex; flex-direction: column; position: relative; }

        /* KỆ HÀNG (SHELF) */
        .shelves-container {
            flex: 2; padding: 10px; display: flex; flex-wrap: wrap; 
            justify-content: center; align-content: center; gap: 10px;
            overflow-y: auto;
        }
        .item {
            width: 70px; height: 70px; background: white; border-radius: 12px;
            box-shadow: 0 4px 0 #bdc3c7; border: 2px solid #ecf0f1;
            display: flex; align-items: center; justify-content: center;
            font-size: 32px; cursor: pointer; transition: 0.1s; position: relative;
        }
        .item:active { transform: scale(0.95); }
        .item.selected { border-color: #27ae60; background: #d5f5e3; opacity: 0.3; pointer-events: none; }
        
        /* ITEM BAY */
        .flying-item {
            position: fixed; width: 50px; height: 50px; background: white; 
            border-radius: 50%; z-index: 100; display: flex; align-items: center; 
            justify-content: center; font-size: 24px; border: 2px solid #2ecc71;
            transition: all 0.5s ease-in-out; pointer-events: none;
        }

        /* BOTTOM PANEL */
        .bottom-panel {
            flex: 1; min-height: 160px; background: #fffdf0; border-top: 5px solid #ff9f43;
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 20px; position: relative;
        }

        .mascot-area { width: 50%; display: flex; align-items: center; gap: 10px; }
        .mascot { height: 90px; }
        .bubble {
            background: white; border: 3px solid #3498db; border-radius: 15px;
            padding: 10px; font-size: 18px; font-weight: 900; color: #2c3e50;
            position: relative; box-shadow: 3px 3px 0 rgba(0,0,0,0.1); width: 100%;
        }
        .bubble::after { content: ''; position: absolute; left: -10px; top: 50%; border: 10px solid transparent; border-right-color: #3498db; transform: translateY(-50%); }

        /* GIỎ HÀNG */
        .basket-zone { width: 40%; text-align: center; position: relative; }
        .basket-img { 
            width: 100px; height: 80px; margin: 0 auto;
            background: url('https://cdn-icons-png.flaticon.com/512/3081/3081840.png') no-repeat center bottom/contain;
        }
        .basket-count {
            position: absolute; top: -5px; right: 20%; background: #e74c3c; color: white;
            width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center;
            justify-content: center; font-weight: bold; border: 2px solid white;
        }
        
        /* NÚT HOÀN THÀNH */
        .btn-finish {
            background: #27ae60; color: white; border: none; padding: 10px 25px;
            border-radius: 50px; font-weight: 900; font-size: 18px; cursor: pointer;
            box-shadow: 0 5px 0 #1e8449; margin-top: 10px; transition: 0.2s;
            animation: pulse 2s infinite; display: inline-block;
        }
        .btn-finish:active { transform: translateY(5px); box-shadow: none; }
        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }

        /* MODAL CHUNG */
        .modal {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.8); z-index: 999; display: none;
            justify-content: center; align-items: center;
        }
        .modal-content {
            background: white; padding: 25px; border-radius: 20px; text-align: center;
            border: 5px solid #f1c40f; width: 90%; max-width: 350px; animation: popIn 0.3s;
        }
        @keyframes popIn { from { transform: scale(0); } to { transform: scale(1); } }

        .btn-modal { padding: 10px 20px; border-radius: 10px; border: none; font-weight: bold; font-size: 16px; cursor: pointer; margin: 5px; }
        .btn-yes { background: #27ae60; color: white; box-shadow: 0 4px 0 #1e8449; }
        .btn-no { background: #e74c3c; color: white; box-shadow: 0 4px 0 #c0392b; }
        .btn-retry { background: #f39c12; color: white; box-shadow: 0 4px 0 #d35400; }
        
        .result-icon { font-size: 60px; margin-bottom: 10px; display: block; }
    </style>
</head>
<body>

    <div class="header-ui">
        <a href="index.php" class="btn-home"><i class="fas fa-arrow-left"></i> Thoát</a>
        <div class="level-badge">Câu: <span id="currentLevel">1</span>/10</div>
        <div class="score-box"><i class="fas fa-star"></i> <span id="score">0</span></div>
    </div>

    <div class="game-area">
        <div class="shelves-container" id="shelf"></div>

        <div class="bottom-panel">
            <div class="mascot-area">
                <img src="https://cdn-icons-png.flaticon.com/512/4475/4475009.png" class="mascot">
                <div class="bubble" id="questionText">...</div>
            </div>

            <div class="basket-zone">
                <div class="basket-img"></div>
                <div class="basket-count" id="basketCount">0</div>
                
                <button class="btn-finish" onclick="askConfirm()">Xong rồi <i class="fas fa-check"></i></button>
                <div style="font-size:12px; color:#555; margin-top:5px; cursor:pointer; text-decoration:underline;" onclick="resetBasket()">Chọn lại</div>
            </div>
        </div>
    </div>

    <div class="modal" id="confirmModal">
        <div class="modal-content">
            <h2>Bé chắc chưa?</h2>
            <p>Kiểm tra kỹ giỏ hàng nha!</p>
            <button class="btn-modal btn-no" onclick="closeModal('confirmModal')">Để bé xem lại</button>
            <button class="btn-modal btn-yes" onclick="checkResult()">Chắc chắn!</button>
        </div>
    </div>

    <div class="modal" id="resultModal">
        <div class="modal-content">
            <div id="resultIcon" class="result-icon"></div>
            <h2 id="resultTitle"></h2>
            <p id="resultMsg"></p>
            <button class="btn-modal btn-yes" onclick="nextLevel()">Câu tiếp theo <i class="fas fa-arrow-right"></i></button>
        </div>
    </div>

    <div class="modal" id="finalModal">
        <div class="modal-content" style="border-color: #e74c3c;">
            <div class="result-icon">🎉</div>
            <h1 style="color: #c0392b;">HOÀN THÀNH!</h1>
            <p style="font-size: 18px;">Tổng điểm của bé:</p>
            <div style="font-size: 40px; font-weight: 900; color: #f1c40f; margin-bottom: 20px;">
                <span id="finalScore">0</span> điểm
            </div>
            <button class="btn-modal btn-retry" onclick="window.location.reload()">Chơi lại</button>
            <button class="btn-modal btn-yes" onclick="window.location='index.php'">Về trang chủ</button>
        </div>
    </div>

    <script>
        // DỮ LIỆU GAME
        const allItems = [
            { id: 'tao', name: 'Quả Táo', icon: 'fa-apple-alt', color: '#e74c3c' },
            { id: 'chanh', name: 'Quả Chanh', icon: 'fa-lemon', color: '#f1c40f' },
            { id: 'ot', name: 'Quả Ớt', icon: 'fa-pepper-hot', color: '#c0392b' },
            { id: 'co', name: 'Cỏ', icon: 'fa-leaf', color: '#2ecc71' },
            { id: 'ca', name: 'Con Cá', icon: 'fa-fish', color: '#3498db' },
            { id: 'banh', name: 'Bánh', icon: 'fa-cookie', color: '#d35400' },
            { id: 'keo', name: 'Kẹo', icon: 'fa-candy-cane', color: '#e84393' },
            { id: 'kem', name: 'Kem', icon: 'fa-ice-cream', color: '#9b59b6' }
        ];

        let level = 1;
        const maxLevel = 10;
        let score = 0;
        
        // Trạng thái màn hiện tại
        let targetItem = null;
        let targetQty = 0;
        let basketItems = []; // Danh sách item bé đã chọn

        // Âm thanh
        function speak(text) {
            window.speechSynthesis.cancel();
            let msg = new SpeechSynthesisUtterance(text);
            msg.lang = 'vi-VN';
            msg.rate = 0.8; 
            window.speechSynthesis.speak(msg);
        }

        window.onload = function() {
            startLevel();
        };

        function startLevel() {
            // Reset dữ liệu màn chơi
            basketItems = [];
            updateBasketUI();
            document.getElementById('currentLevel').innerText = level;
            const shelf = document.getElementById('shelf');
            shelf.innerHTML = '';

            // 1. TÍNH ĐỘ KHÓ (Tăng dần theo level)
            // Level 1-3: Số lượng 1-3, ít đồ nhiễu
            // Level 4-7: Số lượng 2-5, nhiều đồ nhiễu
            // Level 8-10: Số lượng 3-6, rất nhiều đồ nhiễu

            targetQty = Math.floor(Math.random() * 3) + 1 + Math.floor(level / 3); 
            if(targetQty > 6) targetQty = 6; // Tối đa 6 cái thôi ko màn hình chật

            // Chọn ngẫu nhiên 1 vật phẩm cần mua
            targetItem = allItems[Math.floor(Math.random() * allItems.length)];

            // Số lượng đồ nhiễu (đồ sai)
            let wrongItemsCount = 3 + level; 

            // 2. TẠO DANH SÁCH ĐỒ TRÊN KỆ
            let shelfData = [];
            
            // Thêm đồ ĐÚNG (Có thể thêm dư để bẫy bé)
            let actualCorrectOnShelf = targetQty + Math.floor(Math.random() * 2); // Có thể dư 0 hoặc 1 cái
            for(let i=0; i<actualCorrectOnShelf; i++) shelfData.push(targetItem);

            // Thêm đồ SAI
            for(let i=0; i<wrongItemsCount; i++) {
                let randomWrong = allItems[Math.floor(Math.random() * allItems.length)];
                // Nếu trùng đồ đúng thì chọn lại (để không bị lẫn lộn)
                while(randomWrong.id === targetItem.id) {
                     randomWrong = allItems[Math.floor(Math.random() * allItems.length)];
                }
                shelfData.push(randomWrong);
            }

            // Trộn lộn xộn
            shelfData.sort(() => Math.random() - 0.5);

            // 3. HIỂN THỊ LÊN KỆ
            shelfData.forEach((item, index) => {
                let div = document.createElement('div');
                div.className = 'item';
                div.innerHTML = `<i class="fas ${item.icon}" style="color: ${item.color}"></i>`;
                div.onclick = function() { addToBasket(item, div); };
                shelf.appendChild(div);
            });

            // 4. HIỂN THỊ CÂU HỎI
            let txt = `Bé lấy giúp tớ <b style="color:red; font-size:24px;">${targetQty}</b> cái <b style="color:${targetItem.color}">${targetItem.name}</b> nhé!`;
            document.getElementById('questionText').innerHTML = txt;
            
            speak(`Bé lấy giúp tớ ${targetQty} cái ${targetItem.name}`);
        }

        function addToBasket(item, element) {
            // Thêm vào mảng giỏ hàng (Chỉ lưu Logic)
            basketItems.push(item);
            
            // Hiệu ứng bay
            let clone = document.createElement('div');
            clone.className = 'flying-item';
            clone.innerHTML = element.innerHTML;
            let rect = element.getBoundingClientRect();
            let basketRect = document.querySelector('.basket-img').getBoundingClientRect();
            
            clone.style.left = rect.left + 'px';
            clone.style.top = rect.top + 'px';
            document.body.appendChild(clone);

            // Ẩn đồ trên kệ (Để bé biết đã lấy rồi)
            element.classList.add('selected');

            // Bay vào giỏ
            setTimeout(() => {
                clone.style.left = (basketRect.left + 25) + 'px';
                clone.style.top = (basketRect.top + 25) + 'px';
                clone.style.opacity = 0;
                clone.style.transform = 'scale(0.5)';
            }, 50);

            setTimeout(() => {
                clone.remove();
                updateBasketUI();
            }, 500);
        }

        function updateBasketUI() {
            document.getElementById('basketCount').innerText = basketItems.length;
        }

        function resetBasket() {
            // Trả đồ lại kệ
            basketItems = [];
            updateBasketUI();
            let itemsOnShelf = document.querySelectorAll('.item');
            itemsOnShelf.forEach(el => el.classList.remove('selected'));
        }

        // --- XỬ LÝ KIỂM TRA ---
        function askConfirm() {
            if(basketItems.length === 0) {
                speak("Giỏ hàng đang trống kìa bé ơi!");
                return;
            }
            document.getElementById('confirmModal').style.display = 'flex';
            speak("Bé chắc chắn chưa?");
        }

        function closeModal(id) {
            document.getElementById(id).style.display = 'none';
        }

        function checkResult() {
            closeModal('confirmModal');
            
            // LOGIC CHẤM ĐIỂM NGHIÊM NGẶT
            // 1. Phải ĐỦ số lượng
            // 2. Không được THỪA
            // 3. Không được có món SAI (món nhiễu)
            
            let countCorrect = 0;
            let countWrong = 0;

            basketItems.forEach(item => {
                if(item.id === targetItem.id) {
                    countCorrect++;
                } else {
                    countWrong++;
                }
            });

            let isWin = false;
            let msg = "";

            if (countWrong > 0) {
                // Có đồ lạ
                msg = `Ôi! Bé nhặt nhầm <b>${countWrong}</b> món lạ vào giỏ rồi.`;
                speak("Bé nhặt nhầm món khác rồi.");
            } else if (countCorrect < targetQty) {
                // Thiếu
                msg = `Vẫn còn thiếu! Bé mới lấy <b>${countCorrect}</b> trên tổng số <b>${targetQty}</b> thôi.`;
                speak("Vẫn còn thiếu bé ơi.");
            } else if (countCorrect > targetQty) {
                // Thừa
                msg = `Dư rồi! Chỉ cần <b>${targetQty}</b> mà bé lấy tận <b>${countCorrect}</b> cái.`;
                speak("Bé lấy thừa mất rồi.");
            } else {
                // CHÍNH XÁC HOÀN TOÀN
                isWin = true;
                score += 10;
                document.getElementById('score').innerText = score;
                msg = "Giỏi quá! Bé lấy chính xác luôn.";
                speak("Hoan hô, bé giỏi quá!");
            }

            // Hiển thị kết quả
            let modal = document.getElementById('resultModal');
            let icon = document.getElementById('resultIcon');
            let title = document.getElementById('resultTitle');
            
            if(isWin) {
                icon.innerHTML = '😍';
                title.innerHTML = 'CHÍNH XÁC!';
                title.style.color = '#27ae60';
            } else {
                icon.innerHTML = '😢';
                title.innerHTML = 'TIẾC QUÁ!';
                title.style.color = '#e74c3c';
            }
            document.getElementById('resultMsg').innerHTML = msg;
            modal.style.display = 'flex';
        }

        function nextLevel() {
            closeModal('resultModal');
            level++;
            if(level > maxLevel) {
                finishGame();
            } else {
                startLevel();
            }
        }

        function finishGame() {
            // Gửi điểm về hệ thống
            saveScoreToServer(score);
            
            // Hiển thị bảng tổng kết
            document.getElementById('finalScore').innerText = score;
            document.getElementById('finalModal').style.display = 'flex';
            
            // Lời khen cuối cùng
            if(score >= 80) speak("Bé siêu quá! Thần đồng toán học đây rồi!");
            else speak("Bé cố gắng lần sau nhé!");
        }

        function saveScoreToServer(finalScore) {
            let formData = new FormData();
            formData.append('score', finalScore);
            formData.append('game_name', 'Đi Chợ Tết (10 câu)'); // Tên game để lưu DB
            
            fetch('luu-diem.php', {
                method: 'POST',
                body: formData
            }).then(response => console.log("Đã lưu điểm"));
        }
    </script>
</body>
</html>
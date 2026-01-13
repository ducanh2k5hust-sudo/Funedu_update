<?php session_start(); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Học Số Cơ Bản</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(180deg, #81ecec 0%, #74b9ff 100%);
            margin: 0; padding: 0; height: 100vh;
            display: flex; flex-direction: column; overflow: hidden;
            position: relative;
        }

        /* --- Trang trí nền --- */
        .cloud { position: absolute; color: rgba(255,255,255,0.6); animation: floatCloud 20s infinite linear; z-index: 0; }
        .cloud:nth-child(1) { top: 10%; left: -10%; font-size: 80px; animation-duration: 25s; }
        .cloud:nth-child(2) { top: 20%; left: -20%; font-size: 120px; animation-duration: 35s; animation-delay: 5s; }
        @keyframes floatCloud { from { left: -20%; } to { left: 120%; } }

        /* --- Header --- */
        .top-bar { padding: 15px; position: relative; z-index: 10; display: flex; justify-content: space-between; }
        .home-btn { 
            background: white; color: #333; padding: 8px 15px; 
            border-radius: 20px; text-decoration: none; font-weight: 800; 
            box-shadow: 0 4px 0 rgba(0,0,0,0.1); border: 2px solid #fff;
        }

        /* --- Khu vực chính --- */
        .main-stage {
            flex: 1; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            z-index: 5; position: relative;
        }

        /* Vòng tròn số */
        .number-circle {
            width: 220px; height: 220px; background: white;
            border-radius: 50%; border: 8px solid white;
            display: flex; align-items: center; justify-content: center;
            font-size: 120px; font-weight: 900; color: #333;
            box-shadow: 0 10px 0 rgba(0,0,0,0.1);
            margin-bottom: 20px; position: relative;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            cursor: pointer;
        }
        .number-circle.pop { transform: scale(1.1); }
        .number-circle:active { transform: scale(0.95); }

        /* Nút loa */
        .audio-btn {
            background: #ff7675; color: white; width: 60px; height: 60px;
            border-radius: 50%; border: none; font-size: 24px;
            box-shadow: 0 5px 0 #d63031; cursor: pointer;
            margin-bottom: 20px; position: absolute; top: -20px; right: -20px; /* Đổi vị trí loa sang góc cho đỡ che */
            display: flex; align-items: center; justify-content: center;
            animation: pulse 2s infinite; pointer-events: none;
        }

        /* --- BÀN TAY (ĐÃ SỬA CSS) --- */
        .hand-container {
            height: 200px; /* Cố định chiều cao để không bị nhảy */
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 10px 0;
        }
        
        .hand-sign { 
            font-size: 160px; /* Tăng kích thước cực đại */
            margin: 0 15px; 
            filter: drop-shadow(0 8px 5px rgba(0,0,0,0.2)); 
            transition: transform 0.2s;
            cursor: default;
            display: flex; 
            gap: 20px; /* Khoảng cách giữa 2 bàn tay */
        }
        
        /* Hiệu ứng lắc lư bàn tay */
        .hand-sign:hover { transform: rotate(10deg) scale(1.1); }

        /* Tên số */
        .number-name {
            background: #0984e3; color: white;
            padding: 15px 50px; border-radius: 20px;
            font-size: 32px; font-weight: 900; /* Chữ to hơn */
            border: 4px solid #74b9ff;
            box-shadow: 0 8px 0 rgba(0,0,0,0.2);
            text-transform: uppercase;
        }

        /* Điều hướng */
        .nav-area {
            display: flex; align-items: center; justify-content: center;
            width: 100%; max-width: 600px; margin-top: 30px;
        }
        .nav-btn {
            background: rgba(255,255,255,0.4); border: 3px solid white;
            color: white; width: 70px; height: 70px; border-radius: 50%;
            font-size: 30px; cursor: pointer; transition: 0.2s;
            display: flex; align-items: center; justify-content: center;
            margin: 0 20px; /* Tách nút xa bàn tay ra chút */
        }
        .nav-btn:hover { background: white; color: #0984e3; transform: scale(1.1); }
        .nav-btn:disabled { opacity: 0.3; cursor: not-allowed; transform: none; }

        .balloon { position: absolute; font-size: 50px; opacity: 0.8; animation: floatUp 5s infinite ease-in-out; }
        @keyframes floatUp { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(255, 118, 117, 0.7); } 70% { box-shadow: 0 0 0 15px rgba(255, 118, 117, 0); } 100% { box-shadow: 0 0 0 0 rgba(255, 118, 117, 0); } }

    </style>
</head>
<body>

    <i class="fas fa-cloud cloud"></i>
    <i class="fas fa-cloud cloud"></i>

    <div class="top-bar">
        <a href="index.php" class="home-btn"><i class="fas fa-arrow-left"></i> Về trang chủ</a>
    </div>

    <div class="main-stage">
        
        <div class="number-circle" id="numDisplay" onclick="playAudio()" style="color: #e84393;">
            0
            <div class="audio-btn"><i class="fas fa-volume-up"></i></div>
        </div>

        <div style="display: flex; align-items: center; justify-content: center; width: 100%; position: relative;">
            <button class="nav-btn" id="btnPrev" onclick="changeNumber(-1)">
                <i class="fas fa-chevron-left"></i>
            </button>

            <div class="hand-container">
                <div class="hand-sign" id="handDisplay">✊</div>
            </div>

            <button class="nav-btn" id="btnNext" onclick="changeNumber(1)">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        <div class="number-name" id="textDisplay" style="margin-top: 10px;">Số Không</div>

    </div>

    <i class="fas fa-star balloon" style="top: 20%; right: 15%; color: #ffeaa7; animation-delay: 1s;"></i>
    <i class="fas fa-paper-plane balloon" style="bottom: 15%; left: 10%; color: white; font-size: 30px;"></i>

    <script>
        // --- DỮ LIỆU ĐÃ ĐƯỢC CHUẨN HÓA LẠI (QUY TẮC 5 + N) ---
        const numberData = [
            { val: 0, text: "Số Không", color: "#e84393", hand: "✊" },
            { val: 1, text: "Số Một",   color: "#0984e3", hand: "☝️" },
            { val: 2, text: "Số Hai",   color: "#fdcb6e", hand: "✌️" },
            { val: 3, text: "Số Ba",    color: "#00b894", hand: "🤟" }, // 3 ngón
            { val: 4, text: "Số Bốn",   color: "#6c5ce7", hand: "🖖" }, // 4 ngón
            { val: 5, text: "Số Năm",   color: "#d63031", hand: "🖐️" },
            // Từ 6 trở đi dùng 2 bàn tay (5 + n) cho chuẩn bài học
            { val: 6, text: "Số Sáu",   color: "#e17055", hand: "🖐️☝️" }, 
            { val: 7, text: "Số Bảy",   color: "#2d3436", hand: "🖐️✌️" }, 
            { val: 8, text: "Số Tám",   color: "#6c5ce7", hand: "🖐️🤟" }, 
            { val: 9, text: "Số Chín",  color: "#0984e3", hand: "🖐️🖖" }, 
            { val: 10, text: "Số Mười", color: "#fdcb6e", hand: "🖐️🖐️" }
        ];

        let currentIndex = 0;

        // CẤU HÌNH GIỌNG ĐỌC
        let synth = window.speechSynthesis;
        let vietnameseVoice = null;

        window.speechSynthesis.onvoiceschanged = function() {
            let voices = synth.getVoices();
            vietnameseVoice = voices.find(v => v.lang.includes('vi'));
        };

        function updateUI() {
            const data = numberData[currentIndex];
            const numDisplay = document.getElementById('numDisplay');
            
            // Cập nhật số
            numDisplay.childNodes[0].nodeValue = data.val + " ";
            numDisplay.style.color = data.color;
            numDisplay.style.borderColor = data.color;
            
            // Cập nhật chữ
            document.getElementById('textDisplay').innerText = data.text;
            document.getElementById('textDisplay').style.backgroundColor = data.color;
            
            // Cập nhật bàn tay
            document.getElementById('handDisplay').innerText = data.hand;

            // Hiệu ứng nảy
            numDisplay.classList.remove('pop');
            void numDisplay.offsetWidth;
            numDisplay.classList.add('pop');
            
            // Hiệu ứng nảy bàn tay
            const hand = document.getElementById('handDisplay');
            hand.style.transform = "scale(0.5)";
            setTimeout(() => { hand.style.transform = "scale(1)"; }, 100);

            // Ẩn/Hiện nút điều hướng
            document.getElementById('btnPrev').disabled = (currentIndex === 0);
            document.getElementById('btnNext').disabled = (currentIndex === numberData.length - 1);
        }

        function changeNumber(step) {
            let newIndex = currentIndex + step;
            if (newIndex >= 0 && newIndex < numberData.length) {
                currentIndex = newIndex;
                updateUI();
                playAudio(); // Tự động đọc
            }
        }

        function playAudio() {
            if (synth.speaking) synth.cancel();

            const text = numberData[currentIndex].text;
            const utterThis = new SpeechSynthesisUtterance(text);
            
            utterThis.lang = 'vi-VN'; 
            utterThis.rate = 0.8; 
            utterThis.pitch = 1.1; 
            
            if (vietnameseVoice) utterThis.voice = vietnameseVoice;

            synth.speak(utterThis);
        }

        updateUI();

        // Hỗ trợ phím bấm
        document.addEventListener('keydown', (e) => {
            if(e.key === "ArrowLeft") changeNumber(-1);
            if(e.key === "ArrowRight") changeNumber(1);
            if(e.key === " " || e.key === "Enter") playAudio();
        });
    </script>
</body>
</html>
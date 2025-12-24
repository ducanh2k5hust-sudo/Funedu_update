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
            cursor: pointer; /* Thêm con trỏ tay để biết là bấm được */
        }
        .number-circle.pop { transform: scale(1.1); }
        .number-circle:active { transform: scale(0.95); }

        /* Nút loa */
        .audio-btn {
            background: #ff7675; color: white; width: 60px; height: 60px;
            border-radius: 50%; border: none; font-size: 24px;
            box-shadow: 0 5px 0 #d63031; cursor: pointer;
            margin-bottom: 20px; position: absolute; top: -30px;
            display: flex; align-items: center; justify-content: center;
            animation: pulse 2s infinite; pointer-events: none; /* Để bấm vào vòng tròn là ăn luôn */
        }

        /* Bàn tay */
        .hand-sign { font-size: 80px; margin: 0 40px; filter: drop-shadow(0 5px 0 rgba(0,0,0,0.2)); }

        /* Tên số */
        .number-name {
            background: #0984e3; color: white;
            padding: 10px 40px; border-radius: 15px;
            font-size: 28px; font-weight: 900;
            border: 3px solid #74b9ff;
            box-shadow: 0 5px 0 rgba(0,0,0,0.2);
            text-transform: uppercase;
        }

        /* Điều hướng */
        .nav-area {
            display: flex; align-items: center; justify-content: center;
            width: 100%; max-width: 600px; margin-top: 30px;
        }
        .nav-btn {
            background: rgba(255,255,255,0.3); border: 2px solid white;
            color: white; width: 60px; height: 60px; border-radius: 50%;
            font-size: 24px; cursor: pointer; transition: 0.2s;
            display: flex; align-items: center; justify-content: center;
        }
        .nav-btn:hover { background: white; color: #0984e3; }
        .nav-btn:disabled { opacity: 0.3; cursor: not-allowed; }

        .balloon { position: absolute; font-size: 50px; opacity: 0.8; animation: floatUp 5s infinite ease-in-out; }
        @keyframes floatUp { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(255, 118, 117, 0.7); } 70% { box-shadow: 0 0 0 15px rgba(255, 118, 117, 0); } 100% { box-shadow: 0 0 0 0 rgba(255, 118, 117, 0); } }

    </style>
</head>
<body>

    <i class="fas fa-cloud cloud"></i>
    <i class="fas fa-cloud cloud"></i>

    <div class="top-bar">
        <a href="index.php" class="home-btn"><i class="fas fa-home"></i> Thoát</a>
    </div>

    <div class="main-stage">
        
        <div class="number-circle" id="numDisplay" onclick="playAudio()" style="color: #e84393;">
            0
            <div class="audio-btn"><i class="fas fa-volume-up"></i></div>
        </div>

        <div style="display: flex; align-items: center; justify-content: center; width: 100%;">
            <button class="nav-btn" id="btnPrev" onclick="changeNumber(-1)">
                <i class="fas fa-chevron-left"></i>
            </button>

            <div class="hand-sign" id="handDisplay">✊</div>

            <button class="nav-btn" id="btnNext" onclick="changeNumber(1)">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        <div class="number-name" id="textDisplay" style="margin-top: 20px;">Số Không</div>

    </div>

    <i class="fas fa-star balloon" style="top: 20%; right: 15%; color: #ffeaa7; animation-delay: 1s;"></i>
    <i class="fas fa-paper-plane balloon" style="bottom: 15%; left: 10%; color: white; font-size: 30px;"></i>

    <script>
        const numberData = [
            { val: 0, text: "Số Không", color: "#e84393", hand: "✊" },
            { val: 1, text: "Số Một",   color: "#0984e3", hand: "☝️" },
            { val: 2, text: "Số Hai",   color: "#fdcb6e", hand: "✌️" },
            { val: 3, text: "Số Ba",    color: "#00b894", hand: "👌" },
            { val: 4, text: "Số Bốn",   color: "#6c5ce7", hand: "🖖" },
            { val: 5, text: "Số Năm",   color: "#d63031", hand: "🖐️" },
            { val: 6, text: "Số Sáu",   color: "#e17055", hand: "🤙" },
            { val: 7, text: "Số Bảy",   color: "#2d3436", hand: "🤞" },
            { val: 8, text: "Số Tám",   color: "#e84393", hand: "👆" }, 
            { val: 9, text: "Số Chín",  color: "#0984e3", hand: "🤚" },
            { val: 10, text: "Số Mười", color: "#fdcb6e", hand: "👐" }
        ];

        let currentIndex = 0;

        // CẤU HÌNH GIỌNG ĐỌC (Web Speech API)
        // Cách này dùng giọng có sẵn trong máy, không cần mạng, không bị Google chặn
        let synth = window.speechSynthesis;
        let vietnameseVoice = null;

        // Chờ trình duyệt tải danh sách giọng đọc
        window.speechSynthesis.onvoiceschanged = function() {
            let voices = synth.getVoices();
            // Tìm giọng tiếng Việt
            vietnameseVoice = voices.find(v => v.lang.includes('vi'));
        };

        function updateUI() {
            const data = numberData[currentIndex];
            const numDisplay = document.getElementById('numDisplay');
            
            numDisplay.childNodes[0].nodeValue = data.val + " "; // Cập nhật số (giữ lại cái icon loa)
            numDisplay.style.color = data.color;
            numDisplay.style.borderColor = data.color;
            
            document.getElementById('textDisplay').innerText = data.text;
            document.getElementById('textDisplay').style.backgroundColor = data.color;
            document.getElementById('handDisplay').innerText = data.hand;

            // Hiệu ứng nảy
            numDisplay.classList.remove('pop');
            void numDisplay.offsetWidth;
            numDisplay.classList.add('pop');

            document.getElementById('btnPrev').disabled = (currentIndex === 0);
            document.getElementById('btnNext').disabled = (currentIndex === numberData.length - 1);
        }

        function changeNumber(step) {
            let newIndex = currentIndex + step;
            if (newIndex >= 0 && newIndex < numberData.length) {
                currentIndex = newIndex;
                updateUI();
                playAudio(); // Tự động đọc khi chuyển
            }
        }

        function playAudio() {
            // Hủy giọng đang đọc dở (nếu có)
            if (synth.speaking) {
                synth.cancel();
            }

            const text = numberData[currentIndex].text;
            const utterThis = new SpeechSynthesisUtterance(text);
            
            // Cấu hình giọng
            utterThis.lang = 'vi-VN'; 
            utterThis.rate = 0.8; // Đọc chậm rãi cho bé nghe
            utterThis.pitch = 1.1; // Giọng cao hơn chút cho vui tai
            
            if (vietnameseVoice) {
                utterThis.voice = vietnameseVoice;
            }

            synth.speak(utterThis);
        }

        updateUI();

        document.addEventListener('keydown', (e) => {
            if(e.key === "ArrowLeft") changeNumber(-1);
            if(e.key === "ArrowRight") changeNumber(1);
            if(e.key === " " || e.key === "Enter") playAudio();
        });
    </script>
</body>
</html>
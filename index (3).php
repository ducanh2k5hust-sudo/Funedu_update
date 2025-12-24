<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Five Edu - Vui Tết Ất Tỵ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* --- BẢNG MÀU TẾT --- */
        :root {
            --tet-red: #d90429;       /* Đỏ tươi */
            --tet-dark-red: #9b2226;  /* Đỏ Trầm */
            --tet-gold: #ffc300;      /* Vàng kim */
            --tet-cream: #fffdf0;     /* Màu kem nền giấy */
            --sidebar-width: 280px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Nunito', 'Comfortaa', sans-serif; 
            background: linear-gradient(135deg, var(--tet-dark-red) 0%, var(--tet-red) 50%, #ef233c 100%);
            display: flex; height: 100vh; overflow: hidden; color: #333;
            position: relative;
        }

        /* --- HIỆU ỨNG RƠI --- */
        #falling-container {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            pointer-events: none; z-index: 1; overflow: hidden;
        }
        .falling-item {
            position: absolute; top: -50px;
            animation: fall linear infinite;
            opacity: 0.8;
        }
        @keyframes fall {
            0% { transform: translateY(0) rotate(0deg); opacity: 1; }
            100% { transform: translateY(110vh) rotate(360deg); opacity: 0; }
        }

        /* --- SIDEBAR --- */
        .sidebar { 
            width: var(--sidebar-width); z-index: 10;
            background-color: var(--tet-dark-red);
            /* Bỏ background url để tránh lỗi, dùng gradient nhẹ */
            background-image: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.3));
            padding: 20px; display: flex; flex-direction: column; 
            border-right: 4px solid var(--tet-gold);
            overflow-y: auto; box-shadow: 5px 0 15px rgba(0,0,0,0.2);
        }
        
        .logo-area { text-align: center; margin-bottom: 25px; position: relative; }
        .dragon-deco { position: absolute; top: -15px; right: 20px; font-size: 40px; color: var(--tet-gold); filter: drop-shadow(2px 2px 0 #d90429); animation: floatDragon 3s ease-in-out infinite alternate; }
        @keyframes floatDragon { from { transform: translateY(0); } to { transform: translateY(-10px); } }

        .logo-text { 
            background: var(--tet-cream); color: var(--tet-red); 
            padding: 12px 20px; border-radius: 25px; font-weight: 900; font-size: 22px; 
            border: 3px solid var(--tet-gold);
            box-shadow: 0 4px 0 var(--tet-dark-red);
            font-family: 'Comfortaa', cursive;
        }

        /* PROFILE */
        .user-profile { text-align: center; margin-bottom: 25px; color: var(--tet-cream); }
        .avatar { 
            width: 80px; height: 80px; background: var(--tet-cream); border-radius: 50%; 
            margin: 0 auto 10px; display: flex; align-items: center; justify-content: center; 
            font-size: 40px; color: var(--tet-red); 
            border: 4px solid var(--tet-gold);
            box-shadow: 0 0 15px var(--tet-gold);
        }
        
        /* MENU */
        .menu-container { display: flex; flex-direction: column; gap: 12px; }
        .menu-item { 
            display: flex; align-items: center; padding: 12px 15px; 
            background: var(--tet-cream); border-radius: 15px; text-decoration: none; 
            color: var(--tet-dark-red); font-weight: 800; cursor: pointer; transition: 0.3s; 
            position: relative; border: 2px solid var(--tet-gold);
            box-shadow: 0 4px 0 rgba(0,0,0,0.1);
        }
        .menu-item:hover { transform: translateY(-3px); box-shadow: 0 6px 0 var(--tet-gold); background: #fff; }
        .menu-item.active { 
            background: linear-gradient(45deg, var(--tet-red), #ff5e62); 
            color: var(--tet-gold); border-color: var(--tet-gold);
        }
        .menu-item.active .menu-icon-box { background: rgba(255,255,255,0.2); color: var(--tet-gold); border: none; }

        .menu-icon-box { width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; border-radius: 10px; margin-right: 12px; background: #ffeaa7; border: 2px solid var(--tet-gold); color: var(--tet-red); }
        .menu-arrow { margin-left: auto; transition: 0.3s; }
        .menu-item.open .menu-arrow { transform: rotate(180deg); }

        .submenu { display: none; padding-left: 15px; margin-top: 8px; border-left: 3px dashed var(--tet-gold); }
        .submenu.show { display: block; }
        .sub-item { display: flex; align-items: center; background: rgba(255,255,255,0.9); color: var(--tet-dark-red); padding: 10px; margin-bottom: 8px; border-radius: 10px; text-decoration: none; font-size: 15px; font-weight: 700; border: 2px solid var(--tet-gold); transition: 0.2s;}
        .sub-item:hover { border-color: var(--tet-red); background: white; transform: translateX(5px); }
        .sub-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; margin-right: 10px; font-size: 14px; border: 1px solid var(--tet-gold); box-shadow: 2px 2px 0 rgba(0,0,0,0.1); }

        /* MAIN CONTENT */
        .main-content { flex: 1; padding: 30px; overflow-y: auto; z-index: 5; }
        
        .header-bar { 
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; 
            background: var(--tet-cream); 
            padding: 15px 30px; border-radius: 20px; 
            border: 4px solid var(--tet-gold);
            box-shadow: 8px 8px 0 var(--tet-dark-red);
            position: relative;
        }
        .header-bar::before, .header-bar::after {
            content: '🏮'; position: absolute; top: 45px; font-size: 35px; filter: drop-shadow(2px 2px 2px rgba(0,0,0,0.3));
        }
        .header-bar::before { left: 20px; transform: rotate(-10deg); }
        .header-bar::after { right: 80px; transform: rotate(10deg); }

        .card-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; margin-bottom: 30px; align-items: start; }
        
        /* --- SỬA LẠI THẺ CARD (FIX LỖI ẢNH) --- */
        .card { 
            background: var(--tet-cream); border-radius: 25px; padding: 30px 20px; text-align: center; 
            border: 4px solid var(--tet-gold);
            box-shadow: 8px 8px 0 var(--tet-dark-red), inset 0 0 20px rgba(255, 195, 0, 0.2);
            transition: all 0.3s; cursor: pointer; text-decoration: none; display: flex; flex-direction: column; align-items: center; position: relative; overflow: hidden;
        }
        /* Thay ảnh lỗi bằng Emoji Hoa Đào 🌸 */
        .card::before { 
            content: '🌸'; position: absolute; top: 5px; left: 5px; 
            font-size: 25px; opacity: 0.6; animation: spinSlow 10s infinite linear;
        } 
        .card::after { 
            content: '🌸'; position: absolute; bottom: 5px; right: 5px; 
            font-size: 25px; opacity: 0.6; animation: spinSlow 10s infinite linear reverse;
        }
        @keyframes spinSlow { 100% { transform: rotate(360deg); } }

        .card:hover { transform: translate(-4px, -4px) scale(1.02); box-shadow: 12px 12px 0 var(--tet-red); z-index: 2; }
        .card-icon { 
            width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 15px; font-size: 35px; 
            border: 4px solid var(--tet-gold); 
            background: white; box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .card-title { font-weight: 900; margin-bottom: 10px; color: var(--tet-dark-red); font-size: 22px; font-family: 'Comfortaa', cursive; }
        .card-desc { font-size: 15px; color: #666; font-weight: 700; line-height: 1.5; }
        
        .card-sub-container { width: 100%; display: none; margin-top: 20px; border-top: 3px dashed var(--tet-gold); padding-top: 20px; }
        .card-sub-container.show { display: block; }
        .card-sub-item { display: flex; align-items: center; padding: 12px; margin-bottom: 10px; background: #fff; border: 2px solid var(--tet-gold); border-radius: 12px; text-decoration: none; color: var(--tet-dark-red); font-weight: 800; font-size: 15px; transition: 0.2s; }
        .card-sub-item:hover { border-color: var(--tet-red); background: var(--tet-gold); color: var(--tet-red); transform: translateX(5px); }

        .bg-orange { color: #e67e22; } .bg-pink { color: #e84393; } .bg-green { color: #27ae60; } .bg-blue { color: #2980b9; } .bg-purple { color: #8e44ad; } .bg-gray { color: #7f8c8d; }

        .sidebar-footer { margin-top: auto; font-size: 14px; text-align: center; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 15px; color: var(--tet-gold); font-weight: bold; border: 2px solid var(--tet-gold); }

        /* NÚT NHẠC */
        .music-btn-main {
            width: 50px; height: 50px; border-radius: 50%;
            background: var(--tet-red); border: 3px solid var(--tet-gold);
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; color: var(--tet-gold); cursor: pointer;
            margin-right: 25px; transition: 0.3s; box-shadow: 3px 3px 0 rgba(0,0,0,0.2);
            z-index: 2;
        }
        .music-btn-main:hover { transform: scale(1.1) rotate(15deg); }
        .music-btn-main.playing {
            background: var(--tet-gold); color: var(--tet-red); border-color: var(--tet-red);
            animation: spin 4s linear infinite, pulseGold 2s infinite;
        }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        @keyframes pulseGold { 0% { box-shadow: 0 0 0 0 rgba(255, 195, 0, 0.7); } 70% { box-shadow: 0 0 0 15px rgba(255, 195, 0, 0); } 100% { box-shadow: 0 0 0 0 rgba(255, 195, 0, 0); } }

        .section-title { color: var(--tet-cream); text-shadow: 2px 2px 0 var(--tet-dark-red); font-family: 'Comfortaa', cursive; }
        .welcome-text { color: var(--tet-cream); font-size: 18px; background: rgba(0,0,0,0.2); padding: 10px 20px; border-radius: 20px; display: inline-block; border: 2px solid var(--tet-gold); }

    </style>
</head>
<body>
    
    <div id="falling-container"></div>

    <audio id="mainAudio" loop>
        <source src="nhac-nen.mp3" type="audio/mpeg">
    </audio>

    <div class="sidebar">
        <div class="logo-area">
            <i class="fas fa-dragon dragon-deco"></i>
            <span class="logo-text"><i class="fas fa-star" style="color: var(--tet-gold);"></i> Five Edu Tết</span>
        </div>

        <div class="user-profile">
            <div class="avatar"><i class="fas fa-graduation-cap"></i></div>
            
            <?php if(isset($_SESSION['fullname'])): ?>
                <div style="font-weight: 800; font-size: 20px; color: var(--tet-gold); margin-bottom: 10px; font-family: 'Comfortaa';">
                    <?php echo $_SESSION['fullname']; ?>
                </div>

                <?php
                if(file_exists('db.php')) {
                    include_once 'db.php';
                    if(isset($conn)) {
                        $uid = $_SESSION['user_id'];
                        $sql_score = "SELECT SUM(score) as total FROM scores WHERE user_id = $uid";
                        $res_score = $conn->query($sql_score);
                        $total_score = 0;
                        if($res_score && $row_score = $res_score->fetch_assoc()) {
                            $total_score = $row_score['total'] ? $row_score['total'] : 0;
                        }
                        echo '<div style="background: linear-gradient(45deg, var(--tet-red), #ff5e62); color: var(--tet-gold); padding: 8px 20px; border-radius: 25px; font-weight: 900; margin-bottom: 15px; display: inline-block; border: 3px solid var(--tet-gold); box-shadow: 0 5px 10px rgba(0,0,0,0.2);">
                                <i class="fas fa-trophy"></i> ' . number_format($total_score) . ' lộc
                              </div><br>';
                    }
                }
                ?>
                <a href="logout.php" style="background: white; color: var(--tet-red); font-weight: 900; text-decoration: none; font-size: 15px; border: 2px solid var(--tet-gold); padding: 8px 15px; border-radius: 20px; display: inline-block; transition:0.2s;">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </a>
            <?php else: ?>
                <div style="font-weight: 800; font-size: 18px; color: var(--tet-gold); margin-bottom: 15px;">Chào bé yêu! <br>Chúc mừng năm mới!</div>
                <div style="display: flex; flex-direction: column; gap: 10px; padding: 0 10px;">
                    <a href="dangnhap.php" style="display: block; padding: 10px 0; text-align: center; background: var(--tet-gold); color: var(--tet-red); text-decoration: none; border-radius: 12px; font-weight: 900; font-size: 15px; border: 2px solid white; box-shadow: 0 4px 0 rgba(0,0,0,0.2);">Đăng nhập nhận Lì xì</a>
                    <a href="dangky.php" style="display: block; padding: 10px 0; text-align: center; background: white; color: var(--tet-red); text-decoration: none; border-radius: 12px; font-weight: 900; font-size: 15px; border: 2px solid var(--tet-gold);">Đăng ký</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="menu-container">
            <?php
            $menuItems = [
                ['id' => 'home', 'title' => 'Trang chính', 'icon' => 'fa-home', 'desc' => 'Về trang chủ', 'color_class' => 'bg-purple', 'active' => true, 'link' => 'index.php'],
                ['id' => 'thanh-tich', 'title' => 'Bảng Vàng Lộc', 'icon' => 'fa-trophy', 'desc' => 'Xem điểm số và huy hiệu.', 'color_class' => 'bg-green', 'active' => false, 'link' => 'thanh-tich.php'],
                ['id' => 'hoc-so', 'title' => 'Học chữ số', 'icon' => 'fa-hashtag', 'desc' => 'Làm quen, ghép số, đếm hình.', 'color_class' => 'bg-orange', 'active' => false, 'link' => '#', 'sub_menu' => [
                    ['title' => 'Học số cơ bản', 'icon' => 'fa-volume-up', 'color' => '#d35400', 'link' => 'hoc-so-co-ban.php'],
                    ['title' => 'Ghép số nhận lì xì', 'icon' => 'fa-puzzle-piece', 'color' => '#c0392b', 'link' => 'ghep-so.php'],
                ]],
                ['id' => 'game', 'title' => 'Trò chơi Tết', 'icon' => 'fa-gamepad', 'desc' => 'Giải trí sau giờ học.', 'color_class' => 'bg-blue', 'active' => false, 'link' => '#', 'sub_menu' => [
                    ['title' => 'Hiệp sĩ Rồng', 'icon' => 'fa-user-knight', 'color' => '#e74c3c', 'link' => 'giai-cuu.php'],
                ]],
                ['id' => 'admin', 'title' => 'Góc Phụ Huynh', 'icon' => 'fa-user-cog', 'desc' => 'Công cụ tạo bài học.', 'color_class' => 'bg-gray', 'active' => false, 'link' => '#', 'sub_menu' => [
                    ['title' => 'Nạp câu hỏi mới', 'icon' => 'fa-file-upload', 'color' => '#34495e', 'link' => 'nap-cau-hoi.php'],
                ]],
            ];

            foreach ($menuItems as $item) {
                $activeClass = $item['active'] ? 'active' : '';
                $hasSub = isset($item['sub_menu']);
                $onclick = $hasSub ? "onclick=\"toggleMenu('{$item['id']}')\"" : "onclick=\"window.location='{$item['link']}'\"";
                $arrow = $hasSub ? '<i class="fas fa-chevron-down menu-arrow"></i>' : '';

                echo "<div>
                    <div class='menu-item $activeClass' $onclick id='menu-{$item['id']}'>
                        <div class='menu-icon-box'><i class='fas {$item['icon']}'></i></div>
                        <span>{$item['title']}</span>
                        $arrow
                    </div>";

                if ($hasSub) {
                    echo "<div class='submenu' id='sub-{$item['id']}'>";
                    foreach ($item['sub_menu'] as $sub) {
                        echo "<a href='{$sub['link']}' class='sub-item'>
                            <div class='sub-icon' style='background-color: {$sub['color']}; border-color: var(--tet-gold);'><i class='fas {$sub['icon']}'></i></div>
                            <span>{$sub['title']}</span>
                        </a>";
                    }
                    echo "</div>";
                }
                echo "</div>";
            }
            ?>
        </div>

        <div class="sidebar-footer">
            <i class="fas fa-gift"></i> Xuân Bính Ngọ 2026 <i class="fas fa-gift"></i><br>
            Chúc mừng năm mới!
        </div>
    </div>

    <div class="main-content">
        <div class="header-bar">
            <div style="font-weight: 900; font-size: 24px; color: var(--tet-dark-red); font-family: 'Comfortaa';">
                <i class="fas fa-sun" style="color: var(--tet-gold);"></i> FIVE EDU DU XUÂN <i class="fas fa-sun" style="color: var(--tet-gold);"></i>
            </div>
            
            <div style="display:flex; align-items:center; position: relative; z-index: 5;">
                <div class="music-btn-main" id="btnMainMusic" onclick="toggleMainMusic()" title="Bật/Tắt nhạc Tết">
                    <i class="fas fa-music"></i>
                </div>
                <i class="fas fa-bell" style="color: var(--tet-dark-red); margin-right: 15px; font-size: 24px;"></i>
                <i class="fas fa-cat" style="color: var(--tet-gold); font-size: 30px; filter: drop-shadow(2px 2px 0 var(--tet-red));"></i>
            </div>
        </div>

        <h2 class="section-title" style="margin-bottom: 15px; font-size: 32px;">Cung Chúc Tân Xuân! 🌸</h2>
        
        <p class="welcome-text" style="margin-bottom: 35px;">
            <?php 
            if(isset($_SESSION['fullname'])) {
                echo "Chúc bé <b>" . $_SESSION['fullname'] . "</b> năm mới chăm ngoan, học giỏi, nhận thật nhiều lì xì nhé!";
            } else {
                echo "Bé ơi, đăng nhập để nhận lì xì điểm số may mắn đầu năm nha!";
            }
            ?>
        </p>
        
        <div class="card-grid">
            <?php
            foreach ($menuItems as $item) {
                if ($item['id'] == 'home') continue;
                $hasSub = isset($item['sub_menu']);
                $onclick = $hasSub ? "onclick=\"toggleCard('{$item['id']}')\"" : "onclick=\"window.location='{$item['link']}'\"";
                
                $iconStyle = "color: var(--tet-red);";

                echo "<div class='card' $onclick>
                    <div class='card-icon'><i class='fas {$item['icon']}' style='$iconStyle'></i></div>
                    <div class='card-title'>{$item['title']}</div>
                    <div class='card-desc'>{$item['desc']}</div>";
                if ($hasSub) {
                    echo "<div class='card-sub-container' id='card-sub-{$item['id']}'>";
                    foreach ($item['sub_menu'] as $sub) {
                        echo "<a href='{$sub['link']}' class='card-sub-item'>
                            <div class='card-sub-icon' style='background-color: {$sub['color']}; border: 2px solid var(--tet-gold);'><i class='fas {$sub['icon']}'></i></div>
                            <span>{$sub['title']}</span>
                        </a>";
                    }
                    echo "</div>";
                }
                echo "</div>";
            }
            ?>
        </div>
    </div>

    <script>
        // --- HIỆU ỨNG RƠI ---
        function createFallingItems() {
            const container = document.getElementById('falling-container');
            const icons = ['🌸', '🌼', '🧧', '💰', '✨'];
            const count = 30;

            for (let i = 0; i < count; i++) {
                const item = document.createElement('div');
                item.classList.add('falling-item');
                item.innerText = icons[Math.floor(Math.random() * icons.length)];
                item.style.left = Math.random() * 100 + 'vw';
                item.style.fontSize = (Math.random() * 20 + 15) + 'px';
                item.style.animationDuration = (Math.random() * 5 + 5) + 's';
                item.style.animationDelay = (Math.random() * 5) + 's';
                container.appendChild(item);
            }
        }
        createFallingItems();

        // XỬ LÝ NHẠC
        var mainAudio = document.getElementById("mainAudio");
        var btnMusic = document.getElementById("btnMainMusic");
        window.onload = function() {
            var promise = mainAudio.play();
            if (promise !== undefined) {
                promise.then(_ => { btnMusic.classList.add('playing'); }).catch(error => { console.log("Autoplay prevented"); });
            }
        };
        function toggleMainMusic() {
            if (mainAudio.paused) { mainAudio.play(); btnMusic.classList.add('playing'); } else { mainAudio.pause(); btnMusic.classList.remove('playing'); }
        }

        // MENU
        function toggleMenu(id) {
            var submenu = document.getElementById('sub-' + id); var menuItem = document.getElementById('menu-' + id);
            var allSubmenus = document.querySelectorAll('.submenu'); allSubmenus.forEach(function(el) { if(el !== submenu) el.classList.remove('show'); });
            var allMenuItems = document.querySelectorAll('.menu-item'); allMenuItems.forEach(function(el) { if(el !== menuItem) el.classList.remove('open'); });
            if (submenu) { submenu.classList.toggle('show'); menuItem.classList.toggle('open'); }
        }
        function toggleCard(id) {
            var subContainer = document.getElementById('card-sub-' + id); var allCardSubs = document.querySelectorAll('.card-sub-container');
            allCardSubs.forEach(function(el) { if(el !== subContainer) el.classList.remove('show'); });
            if (subContainer) { subContainer.classList.toggle('show'); event.stopPropagation(); }
        }
    </script>
</body>
</html>
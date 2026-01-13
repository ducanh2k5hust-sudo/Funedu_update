<?php

$che_do_bao_tri = false; 

if ($che_do_bao_tri) {
    ?>
    <!DOCTYPE html>
    <html lang="vi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Hệ thống đang bảo trì</title>
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
        <style>
            body {
                margin: 0; height: 100vh; display: flex; flex-direction: column;
                align-items: center; justify-content: center;
                background: linear-gradient(135deg, #d90429 0%, #ffc300 100%);
                font-family: 'Nunito', sans-serif; color: #fff; text-align: center;
            }
            .box {
                background: rgba(255, 255, 255, 0.95); color: #333;
                padding: 40px; border-radius: 20px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.3);
                border: 5px solid #d90429;
                max-width: 500px; width: 90%;
            }
            h1 { color: #d90429; margin: 0 0 10px; font-weight: 900; font-size: 28px; }
            p { font-size: 18px; font-weight: 600; color: #555; }
            .icon { font-size: 80px; margin-bottom: 20px; display: block; animation: spin 3s infinite linear; }
            @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        </style>
    </head>
    <body>
        <div class="box">
            <div class="icon">⚙️</div>
            <h1>HỆ THỐNG ĐANG BẢO TRÌ</h1>
            <p>Rồng con đang dọn dẹp nhà cửa để đón Tết.<br>Bé quay lại sau nhé!</p>
        </div>
    </body>
    </html>
    <?php
    exit(); // Dừng trang web tại đây
}
session_start();

// 1. XỬ LÝ CHẾ ĐỘ KHÁCH
if (isset($_GET['guest']) && $_GET['guest'] == 1) {
    $_SESSION['is_guest'] = true; 
}

// 2. CHẶN CỬA
if (!isset($_SESSION['user_id']) && !isset($_SESSION['is_guest'])) {
    header("Location: chao-mung.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Four Edu - Vui Tết Bính Ngọ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@400;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* --- CSS NÚT SỬA HỒ SƠ MỚI (Đã thêm) --- */
        .btn-edit-hoso {
            background: #ffc300; 
            color: #d90429;      
            padding: 10px 20px;  
            border-radius: 30px; 
            text-decoration: none;
            font-weight: 900;    
            font-size: 16px;     
            display: inline-flex;
            align-items: center;
            gap: 8px;            
            border: 3px solid #fff; 
            box-shadow: 0 6px 0 #b35c00; 
            transition: all 0.2s;
            margin-top: 10px;
            /* Ẩn chữ khi sidebar thu nhỏ sẽ được xử lý ở dưới */
            white-space: nowrap; 
        }
        .btn-edit-hoso:hover { transform: translateY(-3px); background: #fff; color: #d90429; box-shadow: 0 8px 0 #b35c00; }
        .btn-edit-hoso:active { transform: translateY(4px); box-shadow: none; }
        .btn-edit-hoso i { animation: wiggle 2s infinite; }
        @keyframes wiggle { 0%, 100% { transform: rotate(0deg); } 25% { transform: rotate(-15deg); } 75% { transform: rotate(15deg); } }

        /* --- BẢNG MÀU TẾT --- */
        :root {
            --tet-red: #d90429;       
            --tet-dark-red: #9b2226;  
            --tet-gold: #ffc300;      
            --tet-cream: #fffdf0;     
            --sidebar-width: 280px;      /* Chiều rộng khi mở */
            --sidebar-collapsed: 90px;   /* Chiều rộng khi đóng */
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Nunito', 'Comfortaa', sans-serif; 
            background: linear-gradient(135deg, var(--tet-dark-red) 0%, var(--tet-red) 50%, #ef233c 100%);
            /* Thay đổi: display block để hỗ trợ fixed sidebar tốt hơn */
            display: block; 
            height: 100vh; overflow: hidden; color: #333;
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

        /* --- SIDEBAR (SỬA ĐỔI ĐỂ ẨN/HIỆN) --- */
        .sidebar { 
            width: var(--sidebar-collapsed); /* Mặc định thu nhỏ */
            height: 100vh;
            position: fixed; /* Cố định bên trái */
            left: 0; top: 0;
            z-index: 1000;
            
            background-color: var(--tet-dark-red);
            background-image: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.3));
            padding: 20px 10px; /* Padding nhỏ hơn khi đóng */
            display: flex; flex-direction: column; 
            border-right: 4px solid var(--tet-gold);
            overflow-y: auto; overflow-x: hidden;
            box-shadow: 5px 0 15px rgba(0,0,0,0.2);
            transition: width 0.3s ease; /* Hiệu ứng trượt */
        }

        /* Khi di chuột vào sidebar: Mở rộng */
        .sidebar:hover {
            width: var(--sidebar-width);
            padding: 20px; /* Trả lại padding cũ */
        }
        
        /* Ẩn các thành phần khi KHÔNG di chuột vào sidebar */
        .sidebar:not(:hover) .logo-text,
        .sidebar:not(:hover) .user-profile > div:not(.avatar), /* Ẩn tên, điểm */
        .sidebar:not(:hover) .user-profile > a, /* Ẩn nút đăng xuất/sửa */
        .sidebar:not(:hover) .menu-item span, /* Ẩn chữ menu */
        .sidebar:not(:hover) .menu-arrow,
        .sidebar:not(:hover) .sidebar-footer,
        .sidebar:not(:hover) .dragon-deco,
        .sidebar:not(:hover) .submenu /* Ẩn menu con đang mở */
        {
            display: none !important;
        }

        /* Canh giữa icon khi thu nhỏ */
        .sidebar:not(:hover) .menu-item {
            justify-content: center;
            padding: 15px 5px;
        }
        .sidebar:not(:hover) .menu-icon-box {
            margin-right: 0;
        }
        .sidebar:not(:hover) .avatar {
            width: 50px; height: 50px; font-size: 20px; /* Avatar nhỏ lại */
        }

        
        /* --- CÁC THÀNH PHẦN BÊN TRONG SIDEBAR (GIỮ NGUYÊN) --- */
        .logo-area { text-align: center; margin-bottom: 25px; position: relative; white-space: nowrap; }
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
            overflow: hidden; 
            transition: 0.3s;
        }
        
        /* MENU */
        .menu-container { display: flex; flex-direction: column; gap: 12px; }
        .menu-item { 
            display: flex; align-items: center; padding: 12px 15px; 
            background: var(--tet-cream); border-radius: 15px; text-decoration: none; 
            color: var(--tet-dark-red); font-weight: 800; cursor: pointer; transition: 0.3s; 
            position: relative; border: 2px solid var(--tet-gold);
            box-shadow: 0 4px 0 rgba(0,0,0,0.1);
            white-space: nowrap; /* Giữ chữ thẳng hàng */
        }
        .menu-item:hover { transform: translateY(-3px); box-shadow: 0 6px 0 var(--tet-gold); background: #fff; }
        .menu-item.active { 
            background: linear-gradient(45deg, var(--tet-red), #ff5e62); 
            color: var(--tet-gold); border-color: var(--tet-gold);
        }
        .menu-item.active .menu-icon-box { background: rgba(255,255,255,0.2); color: var(--tet-gold); border: none; }

        .menu-icon-box { width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; border-radius: 10px; margin-right: 12px; background: #ffeaa7; border: 2px solid var(--tet-gold); color: var(--tet-red); flex-shrink: 0; }
        .menu-arrow { margin-left: auto; transition: 0.3s; }
        .menu-item.open .menu-arrow { transform: rotate(180deg); }

        .submenu { display: none; padding-left: 15px; margin-top: 8px; border-left: 3px dashed var(--tet-gold); }
        .submenu.show { display: block; }
        .sub-item { display: flex; align-items: center; background: rgba(255,255,255,0.9); color: var(--tet-dark-red); padding: 10px; margin-bottom: 8px; border-radius: 10px; text-decoration: none; font-size: 15px; font-weight: 700; border: 2px solid var(--tet-gold); transition: 0.2s;}
        .sub-item:hover { border-color: var(--tet-red); background: white; transform: translateX(5px); }
        .sub-icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; margin-right: 10px; font-size: 14px; border: 1px solid var(--tet-gold); box-shadow: 2px 2px 0 rgba(0,0,0,0.1); }

        /* MAIN CONTENT - ĐIỀU CHỈNH ĐỂ TRÁNH SIDEBAR FIXED */
        .main-content { 
            /* Đẩy nội dung sang phải bằng chiều rộng sidebar thu nhỏ */
            margin-left: var(--sidebar-collapsed); 
            padding: 30px; 
            height: 100vh;
            overflow-y: auto; 
            z-index: 5; 
            /* Chiều rộng còn lại */
            width: calc(100% - var(--sidebar-collapsed));
            transition: margin-left 0.3s ease; /* Nếu muốn content bị đẩy thì dùng cái này, nhưng overlay (như hiện tại) thì ko cần */
        }
        
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
        
    
        .card { 
            background: var(--tet-cream); border-radius: 25px; padding: 30px 20px; text-align: center; 
            border: 4px solid var(--tet-gold);
            box-shadow: 8px 8px 0 var(--tet-dark-red), inset 0 0 20px rgba(255, 195, 0, 0.2);
            transition: all 0.3s; cursor: pointer; text-decoration: none; display: flex; flex-direction: column; align-items: center; position: relative; overflow: hidden;
        }
      
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

        .sidebar-footer { margin-top: auto; font-size: 14px; text-align: center; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 15px; color: var(--tet-gold); font-weight: bold; border: 2px solid var(--tet-gold); white-space: nowrap; }

        /* NÚT NHẠC */
        .music-btn-main {
            width: 60px; height: 60px; border-radius: 50%;
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

        /* --- FOOTER CREDIT --- */
        .footer-credit {
            position: fixed;
            bottom: 15px;
            right: 20px;
            z-index: 100;
            font-family: 'Comfortaa', cursive;
            font-size: 13px;
            color: var(--tet-gold);
            font-weight: 700;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
            padding: 8px 15px;
            background: rgba(155, 34, 38, 0.8);
            border-radius: 20px;
            border: 2px solid var(--tet-gold);
            backdrop-filter: blur(5px);
            pointer-events: none;
            display: flex; align-items: center; gap: 5px;
        }

       
        .ai-widget-container {
            position: fixed;
            bottom: 60px; 
            right: 20px;
            z-index: 1000;
            font-family: 'Comfortaa', sans-serif;
        }

        /* Nút Rồng + Thẻ tên */
        .ai-toggle-btn {
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            justify-content: flex-end; 
        }

        .ai-toggle-btn:hover {
            transform: scale(1.05);
        }

        .ai-label {
            background: linear-gradient(135deg, var(--tet-gold), #ffd54f);
            color: var(--tet-red);
            padding: 8px 20px;
            border-radius: 20px 0 0 20px;
            font-weight: 900;
            font-size: 14px;
            box-shadow: -2px 4px 10px rgba(0,0,0,0.2);
            margin-right: -15px; 
            padding-right: 25px;
            border: 2px solid var(--tet-red);
            white-space: nowrap;
        }

        .ai-avatar {
            width: 70px;
            height: 70px;
            background: var(--tet-cream);
            border: 3px solid var(--tet-red);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            position: relative;
            z-index: 2;
        }

        .ai-avatar img {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        /* Khung Chat */
        .chat-window {
            position: absolute;
            bottom: 85px;
            right: 0;
            width: 320px;
            background: var(--tet-cream);
            border-radius: 15px;
            box-shadow: 0 5px 30px rgba(0,0,0,0.5);
            display: none; /* Ẩn mặc định */
            flex-direction: column;
            border: 3px solid var(--tet-gold);
            overflow: hidden;
            animation: popUp 0.3s ease-out;
        }

        @keyframes popUp {
            from { opacity: 0; transform: translateY(20px) scale(0.9); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .chat-header {
            background: var(--tet-red);
            color: var(--tet-gold);
            padding: 15px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--tet-gold);
        }

        .chat-body {
            height: 300px;
            padding: 15px;
            overflow-y: auto;
            background: #fffdf0;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .message {
            max-width: 80%;
            padding: 10px 15px;
            border-radius: 15px;
            font-size: 14px;
            line-height: 1.4;
            font-weight: 700;
        }

        .bot-msg {
            background: #ffeaa7; /* Vàng nhạt */
            color: #d35400;
            align-self: flex-start;
            border: 2px solid var(--tet-gold);
            border-radius: 15px 15px 15px 0;
        }

        .user-msg {
            background: var(--tet-red);
            color: white;
            align-self: flex-end;
            border-radius: 15px 15px 0 15px;
        }

        .chat-input-area {
            padding: 10px;
            border-top: 2px solid var(--tet-gold);
            display: flex;
            align-items: center;
            gap: 5px;
            background: white;
        }

        #userInput {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 20px;
            outline: none;
            font-family: 'Nunito';
        }

        .icon-btn {
            width: 40px;
            height: 40px;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 16px;
            transition: background 0.2s;
        }

        #sendBtn {
            background: var(--tet-red);
            color: white;
        }

        #micBtn {
            background: #eeeeee;
            color: #555;
            border: 1px solid #ccc;
        }
        
        #micBtn.listening {
            background: #ff4444;
            color: white;
            animation: pulseMic 1.5s infinite;
            border: none;
        }

        @keyframes pulseMic {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 68, 68, 0.7); }
            70% { transform: scale(1.1); box-shadow: 0 0 0 10px rgba(255, 68, 68, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 68, 68, 0); }
        }


.sidebar::-webkit-scrollbar, 
.main-content::-webkit-scrollbar,
body::-webkit-scrollbar {
    display: none;
}


.sidebar, .main-content, body {
    -ms-overflow-style: none;  /* IE and Edge */
    scrollbar-width: none;  /* Firefox */
}
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
            <span class="logo-text"><i class="fas fa-star" style="color: var(--tet-gold);"></i> FOUR EDU </span>
        </div>

        <div class="user-profile">
            <div class="avatar">
                <?php 
          
                if(isset($_SESSION['avatar']) && $_SESSION['avatar'] != '') {
                    echo "<img src='".$_SESSION['avatar']."' style='width:100%; height:100%; object-fit:cover; border-radius:50%;'>";
                } else {
                    echo '<i class="fas fa-user-astronaut"></i>'; 
                }
                ?>
            </div>
            
            <?php if(isset($_SESSION['fullname'])): ?>
                <div style="font-weight: 900; font-size: 22px; color: var(--tet-gold); margin-bottom: 5px; text-shadow: 2px 2px 0 #9b2226;">
                    <?php echo $_SESSION['fullname']; ?>
                </div>
                
                <a href="ho-so.php" class="btn-edit-hoso">
                    <i class="fas fa-pencil-alt"></i> Sửa hồ sơ
                </a>
                
                <br><br>

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
                    <a href="dangnhap.php" style="display: block; padding: 10px 0; text-align: center; background: var(--tet-gold); color: var(--tet-red); text-decoration: none; border-radius: 12px; font-weight: 900; font-size: 15px; border: 2px solid white; box-shadow: 0 4px 0 rgba(0,0,0,0.2);">Đăng nhập học tập để nhận Lì xì nào !</a>
                    <a href="dangky.php" style="display: block; padding: 10px 0; text-align: center; background: white; color: var(--tet-red); text-decoration: none; border-radius: 12px; font-weight: 900; font-size: 15px; border: 2px solid var(--tet-gold);">Đăng ký</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="menu-container">
            <?php
            $menuItems = [
                ['id' => 'home', 'title' => 'Trang chính', 'icon' => 'fa-home', 'desc' => 'Về trang chủ', 'color_class' => 'bg-purple', 'active' => true, 'link' => 'index.php'],
                
                ['id' => 'thanh-tich', 'title' => 'Bảng Vàng Tết ', 'icon' => 'fa-trophy', 'desc' => 'Xem điểm số và huy hiệu.', 'color_class' => 'bg-green', 'active' => false, 'link' => 'thanh-tich.php'],
                
                ['id' => 'shop', 'title' => 'Cửa Hàng Lì Xì', 'icon' => 'fa-store', 'desc' => 'Dùng điểm đổi quà.', 'color_class' => 'bg-pink', 'active' => false, 'link' => 'shop-li-xi.php'],
                
                ['id' => 'hoc-so', 'title' => 'Học chữ số', 'icon' => 'fa-hashtag', 'desc' => 'Làm quen, ghép số, đếm hình.', 'color_class' => 'bg-orange', 'active' => false, 'link' => '#', 'sub_menu' => [
                    ['title' => 'Học số cơ bản', 'icon' => 'fa-volume-up', 'color' => '#d35400', 'link' => 'hoc-so-co-ban.php'],
                    ['title' => 'Ghép số nhận lì xì', 'icon' => 'fa-puzzle-piece', 'color' => '#c0392b', 'link' => 'ghep-so.php'],
                    ['title' => 'Bé tập so sánh', 'icon' => 'fa-balance-scale', 'color' => '#f39c12', 'link' => 'so-sanh.php'],
                ]],
                
                ['id' => 'game', 'title' => 'Trò chơi Tết', 'icon' => 'fa-gamepad', 'desc' => 'Giải trí sau giờ học.', 'color_class' => 'bg-blue', 'active' => false, 'link' => '#', 'sub_menu' => [
                    ['id' => 'cho-tet', 'title' => 'Chợ Tết Tuổi Thơ', 'icon' => 'fa-shopping-basket', 'desc' => 'Giúp Rồng nhỏ đi siêu thị mua đồ Tết.', 'color_class' => 'bg-pink', 'active' => false, 'link' => 'cho-tet.php'],
                    ['title' => 'Hiệp sĩ Rồng', 'icon' => 'fa-user-knight', 'color' => '#e74c3c', 'link' => 'giai-cuu.php'],
                    ['title' => 'Đường Đua Rồng', 'icon' => 'fa-chess-board', 'color' => '#8e44ad', 'link' => 'game-duong-dua-rong.php'],
                ]],
          
                ['title' => 'AI Thử tài giơ tay', 'icon' => 'fa-hand-paper', 'color' => '#00b894', 'link' => 'game-camera.php'],
                
               
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
                <i class="fas fa-sun" style="color: var(--tet-gold);"></i> FOUR EDU CHÚC MỪNG NĂM MỚI QUÝ PHỤ HUYNH VÀ CÁC BÉ <i class="fas fa-sun" style="color: var(--tet-gold);"></i>
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
                echo "Bé ơi, đăng nhập để học tập và kiếm thật nhiều lì xì điểm số may mắn đầu năm nha!";
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

    <div class="footer-credit">
        <i class="fas fa-sparkles"></i> 🧧 Một sản phẩm của DA Group 🌸
    </div>

    <div class="ai-widget-container">
        <div class="chat-window" id="chatWindow">
            <div class="chat-header">
                <span><i class="fas fa-dragon"></i> Rồng Con Hỗ Trợ</span>
                <span style="cursor:pointer" onclick="toggleChat()">✖</span>
            </div>
            <div class="chat-body" id="chatBody">
                <div class="message bot-msg">Chào bé! Rồng con đây. Bé ấn vào micro để nói chuyện với tớ nhé! 🌸</div>
            </div>
            <div class="chat-input-area">
                <input type="text" id="userInput" placeholder="Nhập hoặc nói..." onkeypress="handleEnter(event)">
                <button class="icon-btn" id="micBtn" onclick="toggleVoice()"><i class="fas fa-microphone"></i></button>
                <button class="icon-btn" id="sendBtn" onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>

        <div class="ai-toggle-btn" onclick="toggleChat()">
            <div class="ai-label">Rồng con hỗ trợ</div>
           <div class="ai-avatar">
   <img src="https://cdn-icons-png.flaticon.com/128/4475/4475009.png" 
         alt="Rồng con AI" 
         style="width: 100%; height: 100%; object-fit: contain; padding: 5px; animation: bounceDragon 2s infinite ease-in-out;">
</div>

<style>
    @keyframes bounceDragon {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-6px); filter: drop-shadow(0 5px 8px rgba(217, 4, 41, 0.3)); }
    }
</style>
            </div>
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

        
        var mainAudio = document.getElementById("mainAudio");
        
  
        mainAudio.volume = 0.5; 
        // ------------------------------------------

        var btnMusic = document.getElementById("btnMainMusic");
        window.onload = function() {
            //  tự động phát nhạc
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

        /* -------------------------------------------
           SCRIPT XỬ LÝ VOICE & CHAT
           ------------------------------------------- */
        
        // 1. Cấu hình Speech API
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        const synthesis = window.speechSynthesis;
        let recognition;
        let isListening = false;

        if (SpeechRecognition) {
            recognition = new SpeechRecognition();
            recognition.lang = 'vi-VN'; 
            recognition.continuous = false;
            
            recognition.onstart = function() {
                isListening = true;
                document.getElementById('micBtn').classList.add('listening');
                document.getElementById('userInput').placeholder = "Rồng đang nghe...";
            };

            recognition.onend = function() {
                isListening = false;
                document.getElementById('micBtn').classList.remove('listening');
                document.getElementById('userInput').placeholder = "Nhập hoặc nói...";
                
                // Nếu nhận được text thì tự gửi luôn
                if(document.getElementById('userInput').value.trim() !== "") {
                     sendMessage();
                }
            };

            recognition.onresult = function(event) {
                const transcript = event.results[0][0].transcript;
                document.getElementById('userInput').value = transcript;
            };
        } else {
            console.log("Trình duyệt không hỗ trợ Speech Recognition");
            document.getElementById('micBtn').style.display = 'none'; // Ẩn nút mic nếu không hỗ trợ
        }

        function toggleChat() {
            const chatWindow = document.getElementById('chatWindow');
            if (chatWindow.style.display === 'flex') {
                chatWindow.style.display = 'none';
            } else {
                chatWindow.style.display = 'flex';
                document.getElementById('userInput').focus();
            }
        }

        function toggleVoice() {
            if (!recognition) {
                alert("Trình duyệt bé đang dùng không hỗ trợ giọng nói rồi!");
                return;
            }
            if (isListening) {
                recognition.stop();
            } else {
                recognition.start();
            }
        }

        function handleEnter(e) {
            if (e.key === 'Enter') sendMessage();
        }

       function speakText(text) {
            if (synthesis.speaking) {
                synthesis.cancel();
            }
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'vi-VN';
            
            // --- SỬA Ở ĐÂY ---
            utterance.rate = 0.6;  // Giảm tốc độ nói (Mặc định là 1.0)
            // ----------------
            
            utterance.pitch = 1.0; // Giữ nguyên giọng cao
            synthesis.speak(utterance);
        }
        async function sendMessage() {
            const input = document.getElementById('userInput');
            const body = document.getElementById('chatBody');
            const text = input.value.trim();

            if (!text) return;

            // Hiển thị tin nhắn người dùng
            body.innerHTML += `<div class="message user-msg">${text}</div>`;
            input.value = '';
            body.scrollTop = body.scrollHeight;

            // Hiển thị loading
            const loadingId = 'loading-' + Date.now();
            body.innerHTML += `<div class="message bot-msg" id="${loadingId}" style="font-style:italic">Rồng đang suy nghĩ... 🌸</div>`;
            body.scrollTop = body.scrollHeight;

            // Gửi sang PHP (file ai_api.php)
            try {
                // Lưu ý: Đảm bảo bạn có file ai_api.php cùng thư mục
                const res = await fetch('ai_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: text })
                });
                const data = await res.json();
                
                // Xóa loading
                document.getElementById(loadingId).remove();
                
                // Hiển thị trả lời
                body.innerHTML += `<div class="message bot-msg">${data.reply}</div>`;
                
                // Đọc to câu trả lời
                speakText(data.reply);
                
            } catch (err) {
                document.getElementById(loadingId).remove();
                body.innerHTML += `<div class="message bot-msg">Rồng nhỏ bị lỗi kết nối rồi, bé thử lại sau nhé!</div>`;
                console.error(err);
            }
            body.scrollTop = body.scrollHeight;
        }
    </script>
</body>
</html>
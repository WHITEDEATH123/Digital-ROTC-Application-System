<?php
/**
 * Main Landing Page - Digital ROTC System
 * Provides separate access for Students and Administrators
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital ROTC System - Jose Hernandez College</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: url('assets/img/ro.png') center/cover no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        /* Removed overlay for 100% background opacity */
        
        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            max-width: 900px;
            width: 90%;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 1;
        }
        
        .header {
            background: linear-gradient(135deg, #2d5016 0%, #4a7c59 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .logo {
            width: 100px;
            height: 100px;
            margin: 0 auto 20px;
            background: white;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .content {
            padding: 50px 40px;
        }
        
        .access-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 30px;
        }
        
        .access-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 40px 30px;
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .access-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            border-color: #007bff;
        }
        
        .access-card.student {
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
        }
        
        .access-card.admin {
            background: linear-gradient(135deg, #fff3e0 0%, #fce4ec 100%);
        }
        
        .access-card .icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        
        .student .icon {
            background: linear-gradient(135deg, #2196f3 0%, #9c27b0 100%);
        }
        
        .admin .icon {
            background: linear-gradient(135deg, #ff9800 0%, #e91e63 100%);
        }
        
        .access-card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: #333;
        }
        
        .access-card p {
            color: #666;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .btn-student {
            background: linear-gradient(135deg, #2d5016 0%, #4a7c59 100%);
            color: white;
        }
        
        .btn-admin {
            background: linear-gradient(135deg, #1b5e20 0%, #388e3c 100%);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .features {
            margin-top: 40px;
            text-align: center;
        }
        
        .features h3 {
            color: #333;
            margin-bottom: 20px;
        }
        
        .feature-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .feature-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #007bff;
        }
        
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            border-top: 1px solid #eee;
        }
        
        @media (max-width: 768px) {
            body {
                background-size: cover;
                background-position: center;
            }
            
            .container {
                width: 95%;
                margin: 10px;
            }
            
            .access-options {
                grid-template-columns: 1fr;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .content {
                padding: 30px 20px;
            }
            
            .feature-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <img src="assets/img/jhcsc-logo.png" alt="JHCSC Logo">
            </div>
            <h1>Digital ROTC System</h1>
            <p>Jose Hernandez College of Science and Computing</p>
        </div>
        
        <div class="content">
            <div class="access-options">
                <!-- Student Access -->
                <div class="access-card student">
                    <div class="icon">👨‍🎓</div>
                    <h3>Student Portal</h3>
                    <p>Access your enrollment dashboard, submit applications, track status, and manage your ROTC enrollment process.</p>
                    <a href="student/login.php" class="btn btn-student">Student Login</a>
                </div>
                
                <!-- Admin Access -->
                <div class="access-card admin">
                    <div class="icon">👨‍💼</div>
                    <h3>Administrator Portal</h3>
                    <p>Manage enrollments, review applications, approve/reject submissions, and oversee the ROTC program.</p>
                    <a href="admin/login.php" class="btn btn-admin">Admin Login</a>
                </div>
            </div>
            
            <div class="features">
                <h3>System Features</h3>
                <div class="feature-list">
                    <div class="feature-item">
                        <strong>📝 Online Enrollment</strong><br>
                        Complete digital application process
                    </div>
                    <div class="feature-item">
                        <strong>📊 Status Tracking</strong><br>
                        Real-time application monitoring
                    </div>
                    <div class="feature-item">
                        <strong>📄 Document Management</strong><br>
                        Secure file upload and storage
                    </div>
                    <div class="feature-item">
                        <strong>🔒 Secure Access</strong><br>
                        Role-based authentication system
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p>&copy; 2024 Jose Hernandez College of Science and Computing - Digital ROTC System</p>
            <p>For technical support, contact the IT Department</p>
        </div>
    </div>
</body>
</html>

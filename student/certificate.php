<?php
require_once '../config/database.php';

$sessionManager = new SessionManager();
$sessionManager->requireRole('student');

$database = new Database();
$db = $database->getConnection();

$student_id = $_SESSION['user_id'];

// Get enrollment information - only approved enrollments
$query = "SELECT * FROM enrollments WHERE student_id = ? AND status = 'approved' ORDER BY created_at DESC LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute([$student_id]);
$enrollment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$enrollment) {
    header('Location: dashboard.php?error=no_approved_enrollment');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ROTC Enrollment Certificate - <?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?></title>
  <style>
    @media print {
      .no-print { display: none !important; }
      body { margin: 0; }
      .container { box-shadow: none; margin: 0; }
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Times New Roman', serif;
      background: #f5f5f5;
      padding: 20px;
    }
    
    .navbar {
      background: linear-gradient(135deg, #2d5016 0%, #4a7c59 100%);
      color: white;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      margin-bottom: 2rem;
      border-radius: 10px;
    }
    
    .navbar-brand {
      font-size: 1.2rem;
      font-weight: bold;
      display: flex;
      align-items: center;
      gap: 15px;
    }
    
    .navbar-logo {
      height: 40px;
      width: auto;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
      background: white;
      padding: 4px;
    }
    
    .navbar-nav {
      display: flex;
      gap: 15px;
      align-items: center;
    }
    
    .nav-link {
      color: white;
      text-decoration: none;
      padding: 6px 12px;
      border-radius: 15px;
      transition: background-color 0.3s;
      font-size: 14px;
    }
    
    .nav-link:hover {
      background-color: rgba(255,255,255,0.2);
    }
    
    .container {
      max-width: 800px;
      margin: 0 auto;
      background: white;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
      border-radius: 10px;
      overflow: hidden;
    }
    
    .certificate-header {
      background: linear-gradient(135deg, #2d5016 0%, #4a7c59 100%);
      color: white;
      padding: 30px;
      text-align: center;
      position: relative;
    }
    
    .certificate-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
      opacity: 0.3;
    }
    
    .logo-section {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 20px;
      margin-bottom: 20px;
      position: relative;
      z-index: 1;
    }
    
    .logo {
      width: 80px;
      height: 80px;
      background: white;
      border-radius: 15px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 8px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .logo img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }
    
    .institution-info {
      position: relative;
      z-index: 1;
    }
    
    .institution-name {
      font-size: 1.8rem;
      font-weight: bold;
      margin-bottom: 5px;
    }
    
    .institution-subtitle {
      font-size: 1rem;
      opacity: 0.9;
    }
    
    .certificate-title {
      font-size: 2.2rem;
      font-weight: bold;
      margin-top: 20px;
      letter-spacing: 2px;
      position: relative;
      z-index: 1;
    }
    
    .certificate-body {
      padding: 40px;
      line-height: 1.8;
    }
    
    .certificate-text {
      text-align: center;
      font-size: 1.1rem;
      margin-bottom: 30px;
      color: #333;
    }
    
    .student-name {
      font-size: 2rem;
      font-weight: bold;
      color: #2c5530;
      text-decoration: underline;
      margin: 20px 0;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    
    .details-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin: 30px 0;
      background: #f8f9fa;
      padding: 25px;
      border-radius: 10px;
      border: 2px solid #e9ecef;
    }
    
    .detail-item {
      display: flex;
      justify-content: space-between;
      padding: 8px 0;
      border-bottom: 1px dotted #ccc;
    }
    
    .detail-label {
      font-weight: bold;
      color: #555;
    }
    
    .detail-value {
      color: #333;
      font-weight: 500;
    }
    
    .photo-section {
      text-align: center;
      margin: 30px 0;
    }
    
    .student-photo {
      width: 120px;
      height: 120px;
      border: 3px solid #2c5530;
      border-radius: 10px;
      object-fit: cover;
    }
    
    .signatures-section {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 50px;
      margin-top: 50px;
      padding-top: 30px;
      border-top: 2px solid #eee;
    }
    
    .signature-block {
      text-align: center;
    }
    
    .signature-line {
      border-bottom: 2px solid #333;
      height: 50px;
      margin-bottom: 10px;
      position: relative;
    }
    
    .signature-label {
      font-weight: bold;
      color: #333;
      margin-bottom: 5px;
    }
    
    .signature-date {
      font-size: 0.9rem;
      color: #666;
    }
    
    .certificate-footer {
      background: #f8f9fa;
      padding: 20px;
      text-align: center;
      font-size: 0.9rem;
      color: #666;
      border-top: 1px solid #eee;
    }
    
    .print-controls {
      text-align: center;
      padding: 20px;
      background: #f8f9fa;
      border-top: 1px solid #eee;
    }
    
    .btn {
      display: inline-block;
      padding: 12px 24px;
      margin: 0 10px;
      border: none;
      border-radius: 25px;
      text-decoration: none;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .btn-primary {
      background: linear-gradient(135deg, #2d5016 0%, #4a7c59 100%);
      color: white;
    }
    
    .btn-secondary {
      background: #6c757d;
      color: white;
    }
    
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .approval-stamp {
      position: absolute;
      top: 20px;
      right: 20px;
      background: #28a745;
      color: white;
      padding: 10px 15px;
      border-radius: 50px;
      font-weight: bold;
      font-size: 0.9rem;
      transform: rotate(15deg);
      box-shadow: 0 3px 10px rgba(40, 167, 69, 0.3);
    }
    
    @media (max-width: 768px) {
      .details-grid {
        grid-template-columns: 1fr;
      }
      
      .signatures-section {
        grid-template-columns: 1fr;
        gap: 30px;
      }
      
      .navbar {
        padding: 1rem;
      }
    }
  </style>
</head>
<body>
  <!-- Navigation (No Print) -->
  <nav class="navbar no-print">
    <div class="navbar-brand">
      <img src="../assets/img/jhcsc-logo.png" alt="JHCSC Logo" class="navbar-logo">
      <div>
        <div style="font-size: 1rem;">Student Portal</div>
        <div style="font-size: 0.7rem; opacity: 0.9;">Digital ROTC System</div>
      </div>
    </div>
    <div class="navbar-nav">
      <a href="dashboard.php" class="nav-link">Dashboard</a>
      <a href="profile.php" class="nav-link">Profile</a>
      <a href="../index.php" class="nav-link">Logout</a>
    </div>
  </nav>

  <div class="container">
    <!-- Certificate Header -->
    <div class="certificate-header">
      <div class="approval-stamp">APPROVED</div>
      
      <div class="logo-section">
        <div class="logo">
          <img src="../assets/img/jhcsc-logo.png" alt="JHCSC Logo">
        </div>
        <div class="institution-info">
          <div class="institution-name">JOSE HERNANDEZ COLLEGE</div>
          <div class="institution-subtitle">of Science and Computing</div>
        </div>
      </div>
      
      <div class="certificate-title">ENROLLMENT CERTIFICATE</div>
    </div>
    <div class="certificate-body">
      <div class="certificate-text">
        This is to certify that
      </div>
      
      <div class="student-name">
        <?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['middle_name'] . ' ' . $enrollment['last_name']); ?>
      </div>
      
      <div class="certificate-text">
        has been successfully enrolled in the <strong>Reserve Officers' Training Corps (ROTC)</strong> program 
        and has met all the requirements for enrollment as specified by the institution.
      </div>

      <!-- Student Details -->
      <div class="details-grid">
        <div class="detail-item">
          <span class="detail-label">Student ID:</span>
          <span class="detail-value"><?php echo htmlspecialchars($enrollment['student_id']); ?></span>
        </div>
        
        <div class="detail-item">
          <span class="detail-label">Course:</span>
          <span class="detail-value"><?php echo htmlspecialchars($enrollment['course']); ?></span>
        </div>
        
        <div class="detail-item">
          <span class="detail-label">Block:</span>
          <span class="detail-value">Block <?php echo htmlspecialchars($enrollment['block']); ?></span>
        </div>
        
        <div class="detail-item">
          <span class="detail-label">NSTP Program:</span>
          <span class="detail-value"><?php echo htmlspecialchars($enrollment['army_nstp']); ?></span>
        </div>
        
        <div class="detail-item">
          <span class="detail-label">Date of Birth:</span>
          <span class="detail-value"><?php echo date('F j, Y', strtotime($enrollment['date_of_birth'])); ?></span>
        </div>
        
        <div class="detail-item">
          <span class="detail-label">Blood Type:</span>
          <span class="detail-value"><?php echo htmlspecialchars($enrollment['blood_type']); ?></span>
        </div>
        
        <div class="detail-item">
          <span class="detail-label">Height:</span>
          <span class="detail-value"><?php echo $enrollment['height']; ?> cm</span>
        </div>
        
        <div class="detail-item">
          <span class="detail-label">Weight:</span>
          <span class="detail-value"><?php echo $enrollment['weight']; ?> kg</span>
        </div>
        
        <div class="detail-item">
          <span class="detail-label">Enrollment Date:</span>
          <span class="detail-value"><?php echo date('F j, Y', strtotime($enrollment['submitted_at'])); ?></span>
        </div>
        
        <div class="detail-item">
          <span class="detail-label">Approval Date:</span>
          <span class="detail-value"><?php echo date('F j, Y', strtotime($enrollment['reviewed_at'] ?: $enrollment['updated_at'])); ?></span>
        </div>
      </div>

      <!-- Photo Section -->
      <div class="photo-section">
        <?php if (!empty($enrollment['photo_path']) && file_exists($enrollment['photo_path'])): ?>
          <img src="<?php echo htmlspecialchars($enrollment['photo_path']); ?>" 
               alt="Student Photo" class="student-photo">
        <?php else: ?>
          <div style="width: 120px; height: 120px; border: 3px solid #2c5530; border-radius: 10px; display: inline-block; line-height: 120px; text-align: center; color: #666; background: #f8f9fa;">
            No Photo
          </div>
        <?php endif; ?>
      </div>

      <div class="certificate-text">
        This certificate is issued in recognition of the student's commitment to serve and dedication 
        to the principles of leadership, discipline, and patriotism as embodied in the ROTC program.
      </div>

      <!-- Signatures -->
      <div class="signatures-section">
        <div class="signature-block">
          <div class="signature-line"></div>
          <div class="signature-label">ROTC Commandant</div>
          <div class="signature-date">Date: <?php echo date('F j, Y'); ?></div>
        </div>
        
        <div class="signature-block">
          <div class="signature-line"></div>
          <div class="signature-label">Registrar</div>
          <div class="signature-date">Date: <?php echo date('F j, Y'); ?></div>
        </div>
      </div>
    </div>

    <!-- Certificate Footer -->
    <div class="certificate-footer">
      <p><strong>Certificate No:</strong> ROTC-<?php echo date('Y'); ?>-<?php echo str_pad($enrollment['id'], 4, '0', STR_PAD_LEFT); ?></p>
      <p>This certificate is valid and verifiable through the Digital ROTC System</p>
      <p>Jose Hernandez College of Science and Computing | Digital ROTC Enrollment System</p>
    </div>

    <!-- Print Controls (No Print) -->
    <div class="print-controls no-print">
      <button onclick="window.print()" class="btn btn-primary">
        🖨️ Print Certificate
      </button>
      <a href="dashboard.php" class="btn btn-secondary">
        ← Back to Dashboard
      </a>
    </div>
  </div>

  <script>
    // Auto-focus for printing
    window.addEventListener('load', function() {
      // Add print styles
      const style = document.createElement('style');
      style.textContent = `
        @media print {
          @page {
            margin: 0.5in;
            size: letter;
          }
          body {
            background: white !important;
            padding: 0 !important;
          }
          .container {
            box-shadow: none !important;
            border-radius: 0 !important;
            max-width: none !important;
          }
        }
      `;
      document.head.appendChild(style);
    });
  </script>
</body>
</html>

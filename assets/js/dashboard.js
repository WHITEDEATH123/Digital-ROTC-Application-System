(function () {
  const session = JSON.parse(sessionStorage.getItem('rotc_session') || 'null');
  if (!session || session.role !== 'student') {
    alert('Please log in as a student.');
    window.location.href = 'login.html';
  }

  const users = JSON.parse(localStorage.getItem('rotc_users') || '[]');
  const cadetIndex = users.findIndex(u => u.role === 'student' && u.id === session.id);
  const cadet = users[cadetIndex];

  if (!cadet) {
    alert('Student record not found.');
    window.location.href = 'login.html';
  }

  const cadetName = document.getElementById('cadetName');
  const statusTitle = document.getElementById('statusTitle');
  const statusDesc = document.getElementById('statusDesc');
  const enrollNowBtn = document.getElementById('enrollNowBtn');
  const logoutBtn = document.getElementById('logoutBtn');
  const goToReview = document.getElementById('goToReview');
  const remarksBox = document.getElementById('remarksBox');
  const remarkText = document.getElementById('remarkText');
  const printBtn = document.getElementById("printBtn");

  function refreshUI() {
    cadetName.textContent = cadet.profile?.name || cadet.id;

    const status = cadet.status || 'not_enrolled';

    // Show admin remarks if needed
    if (status === 'needs_revision' && cadet.remark) {
      remarksBox.style.display = 'block';
      remarkText.textContent = cadet.remark;
    } else {
      remarksBox.style.display = 'none';
      remarkText.textContent = '';
    }

    // Build extra info string for timestamps
let extraInfo = "";

if (cadet.submissionTime) {
  const subDate = new Date(cadet.submissionTime);
  extraInfo += `Submitted: ${subDate.toLocaleDateString()} ${subDate.toLocaleTimeString()}. `;
}

if (cadet.approvalTime) {
  const appDate = new Date(cadet.approvalTime);
  extraInfo += `Approved: ${appDate.toLocaleDateString()} ${appDate.toLocaleTimeString()}. `;
}

if (cadet.rejectedTime) {
  const rejDate = new Date(cadet.rejectedTime);
  extraInfo += `Rejected: ${rejDate.toLocaleDateString()} ${rejDate.toLocaleTimeString()}. `;
}

if (cadet.remarksTime) {
  const remDate = new Date(cadet.remarksTime);
  extraInfo += `Remarks Sent: ${remDate.toLocaleDateString()} ${remDate.toLocaleTimeString()}. `;
}

    switch (status) {
      case 'not_enrolled':
        statusTitle.textContent = 'Not Yet Enrolled';
        statusDesc.textContent = 'You have not filled an enrollment form. Click Enroll Now to begin.';
        printBtn.style.display = 'none';
        break;
      case 'submitted':
        statusTitle.textContent = 'Form Submitted';
        statusDesc.textContent = `You submitted your enrollment form. Await admin review. ${extraInfo}`;
        printBtn.style.display = 'none';
        break;
      case 'pending':
        statusTitle.textContent = 'Pending Enrollment';
        statusDesc.textContent = `You confirmed your submission. Admin will approve soon. ${extraInfo}`;
        printBtn.style.display = 'none';
        break;
      case 'approved':
  statusTitle.textContent = 'Enrollment Approved';
  statusDesc.textContent = `Approved on: ${cadet.approvedAt || 'N/A'}`;
  printBtn.style.display = "inline-block";
  break;

      case 'needs_revision':
        statusTitle.textContent = 'Needs Revision';
        statusDesc.textContent = `Admin requested changes. Please update your form. ${extraInfo}`;
        printBtn.style.display = 'none';
        break;
      case 'rejected':
        statusTitle.textContent = 'Rejected';
        statusDesc.textContent = `Your application was rejected. Check admin remarks. ${extraInfo}`;
        printBtn.style.display = 'none';
        break;
      default:
        statusTitle.textContent = 'Not Yet Enrolled';
        statusDesc.textContent = 'You have not filled an enrollment form.';
        printBtn.style.display = 'none';
    }
  }

  enrollNowBtn.addEventListener('click', () => {
    window.location.href = 'enroll.html';
  });

  goToReview.addEventListener('click', () => {
    window.location.href = 'review.html';
  });

  logoutBtn.addEventListener('click', () => {
    sessionStorage.removeItem('rotc_session');
    window.location.href = 'login.html';
  });

  printBtn.addEventListener('click', () => {
    sessionStorage.setItem("selectedCadet", cadetIndex);
    window.location.href = "cadetview.html";
  });

  refreshUI();
})();


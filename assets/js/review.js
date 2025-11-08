// review.js
(function () {
  const session = JSON.parse(sessionStorage.getItem('rotc_session') || 'null');
  if (!session || session.role !== 'student') { alert('Please login as student'); window.location.href='login.html'; }

  const users = JSON.parse(localStorage.getItem('rotc_users') || '[]');
  const me = users.find(u => u.role === 'student' && u.id === session.id);
  if (!me) { alert('No student record'); window.location.href='login.html'; }

  const reviewCard = document.getElementById('reviewCard');
  const editBtn = document.getElementById('editBtn');
  const confirmBtn = document.getElementById('confirmBtn');

 function render() {
  const form = me.form || {};
  reviewCard.innerHTML = `
    <h3>Cadet Enrollment Details</h3>
    
    <div class="field-row">
      <div class="field"><strong>First Name:</strong> ${form.firstName || '<em>missing</em>'}</div>
      <div class="field"><strong>Middle Name:</strong> ${form.middleName || '<em>missing</em>'}</div>
      <div class="field"><strong>Last Name:</strong> ${form.lastName || '<em>missing</em>'}</div>
    </div>
    
    <div class="field-row">
      <div class="field"><strong>Age:</strong> ${form.age || '<em>missing</em>'}</div>
      <div class="field"><strong>Religion:</strong> ${form.religion || '<em>missing</em>'}</div>
      <div class="field"><strong>Date of Birth:</strong> ${form.dob || '<em>missing</em>'}</div>
    </div>

    <div class="field-row">
      <div class="field"><strong>Place of Birth:</strong> ${form.pob || '<em>missing</em>'}</div>
      <div class="field"><strong>Height:</strong> ${form.height || '<em>missing</em>'}</div>
      <div class="field"><strong>Weight:</strong> ${form.weight || '<em>missing</em>'}</div>
    </div>

    <div class="field-row">
      <div class="field"><strong>Complexion:</strong> ${form.complexion || '<em>missing</em>'}</div>
      <div class="field"><strong>Blood Type:</strong> ${form.bloodtype || '<em>missing</em>'}</div>
    </div>

    <div class="field-row">
      <div class="field"><strong>Block:</strong> ${form.block || '<em>missing</em>'}</div>
      <div class="field"><strong>Course:</strong> ${form.course || '<em>missing</em>'}</div>
      <div class="field"><strong>Army NSTP:</strong> ${form.armyRotc || '<em>missing</em>'}</div>
    </div>

    <div class="field-row">
      <div class="field"><strong>Address:</strong> ${form.address || '<em>missing</em>'}</div>
      <div class="field"><strong>Phone:</strong> ${form.phone || '<em>missing</em>'}</div>
      <div class="field"><strong>Email:</strong> ${form.email || '<em>missing</em>'}</div>
    </div>

    <h4>Parent/Guardian Information</h4>
    <div class="field-row">
      <div class="field"><strong>Father:</strong> ${form.father || '<em>missing</em>'} (${form.foccupation || 'N/A'})</div>
      <div class="field"><strong>Mother:</strong> ${form.mother || '<em>missing</em>'} (${form.moccupation || 'N/A'})</div>
    </div>

    <div class="field-row">
      <div class="field"><strong>Emergency Contact:</strong> ${form.emergencyPerson || '<em>missing</em>'}</div>
      <div class="field"><strong>Relationship:</strong> ${form.relationship || '<em>missing</em>'}</div>
      <div class="field"><strong>Contact No.:</strong> ${form.emergencyContact || '<em>missing</em>'}</div>
    </div>

    <h4>Military Science Completed</h4>
    <div class="field-row">
      <div class="field"><strong>MS:</strong> ${form.ms || '<em>missing</em>'}</div>
      <div class="field"><strong>Semester:</strong> ${form.semester || '<em>missing</em>'}</div>
      <div class="field"><strong>School Year:</strong> ${form.schoolyear || '<em>missing</em>'}</div>
      <div class="field"><strong>Grade:</strong> ${form.grade || '<em>missing</em>'}</div>
      <div class="field"><strong>Remarks:</strong> ${form.remarks || '<em>missing</em>'}</div>
    </div>

    <div class="field-row">
      <div class="field"><strong>Advance Course:</strong> ${form.advanceCourse ? 'Yes' : 'No'}</div>
    </div>

    <div class="field-row">
      <div class="photo"><img src="${form.photo || ''}" alt="photo" style="max-width:150px;max-height:150px;display:${form.photo ? 'block':'none'}"></div>
    </div>
  `;
}

  editBtn.addEventListener('click', ()=> window.location.href='enroll.html');

  confirmBtn.addEventListener('click', ()=> {
    me.status = 'pending'; // student confirms enrollment -> pending for admin
    // persist
    const all = JSON.parse(localStorage.getItem('rotc_users')||'[]');
    const idx = all.findIndex(u => u.role==='student' && u.id === me.id);
    if (idx >= 0) all[idx] = me;
    localStorage.setItem('rotc_users', JSON.stringify(all));
    alert('You confirmed your enrollment. Status: Pending Enrollment.');
    window.location.href = 'dashboard.html';
  });

  render();
})();

(function () {
  const idx = parseInt(sessionStorage.getItem("selectedCadet"), 10);
  let users = JSON.parse(localStorage.getItem("rotc_users") || "[]");

  if (isNaN(idx) || !users[idx]) {
    alert("Cadet not found.");
    window.history.back();
    return;
  }

  const student = users[idx];
  const form = student.form || student; // ✅ fallback if saved flat

  // === Fill in student data ===
  document.getElementById("fname").value = form.firstName || "";
  document.getElementById("mname").value = form.middleName || "";
  document.getElementById("lname").value = form.lastName || "";
  document.getElementById("age").value = form.age || "";
  document.getElementById("religion").value = form.religion || "";
  document.getElementById("dob").value = form.dob || "";
  document.getElementById("pob").value = form.pob || "";
  document.getElementById("height").value = form.height || "";
  document.getElementById("weight").value = form.weight || "";
  document.getElementById("complexion").value = form.complexion || "";
  document.getElementById("bloodtype").value = form.bloodtype || "";
  document.getElementById("block").value = form.block || "";
  document.getElementById("course").value = form.course || "";
  document.getElementById("army").value = form.armyRotc || "";
  document.getElementById("address").value = form.address || "";
  document.getElementById("phone").value = form.phone || "";
  document.getElementById("email").value = form.email || "";
  document.getElementById("father").value = form.father || "";
  document.getElementById("foccupation").value = form.foccupation || "";
  document.getElementById("mother").value = form.mother || "";
  document.getElementById("moccupation").value = form.moccupation || "";
  document.getElementById("emergencyPerson").value = form.emergencyPerson || "";
  document.getElementById("relationship").value = form.relationship || "";
  document.getElementById("emergencyContact").value = form.emergencyContact || "";
  document.getElementById("ms").value = form.ms || "";
  document.getElementById("semester").value = form.semester || "";
  document.getElementById("schoolyear").value = form.schoolyear || "";
  document.getElementById("grade").value = form.grade || "";
  document.getElementById("msremarks").value = form.remarks || "";
  document.getElementById("advanceCourse").checked = !!form.advanceCourse;

  if (form.photo) {
    document.getElementById("photo").src = form.photo;
  }

  // Remarks textarea
  document.getElementById("remarks").value = student.remark || "";

  // === BUTTON ACTIONS ===
// Approve
document.getElementById("approveBtn").onclick = () => {
  student.status = "approved";
  student.remark = "";
  student.approvalTime = new Date().toISOString(); // ✅ use ISO
  saveAndBack();
};

// Reject
document.getElementById("rejectBtn").onclick = () => {
  student.status = "rejected";
  student.remark = "";
  student.rejectedTime = new Date().toISOString(); // ✅ use ISO
  saveAndBack();
};

// Send Remarks
document.getElementById("sendRemarksBtn").onclick = () => {
  const txt = document.getElementById("remarks").value.trim();
  if (!txt) return alert("Enter remarks first!");
  student.status = "needs_revision";
  student.remark = txt;
  student.remarksTime = new Date().toISOString(); // ✅ use ISO
  saveAndBack();
};

  function saveAndBack() {
    users[idx] = student;
    localStorage.setItem("rotc_users", JSON.stringify(users));
    alert("Changes saved (Status: " + student.status + ")");
    window.location.href = "admin.html";
  }
})();

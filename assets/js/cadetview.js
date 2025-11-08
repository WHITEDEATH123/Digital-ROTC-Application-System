(function () {
  // Get selected cadet index from sessionStorage
  const idx = sessionStorage.getItem("selectedCadet");
  const users = JSON.parse(localStorage.getItem("rotc_users") || "[]");

  if (idx === null || !users[idx]) {
    alert("Cadet data not found.");
    window.location.href = "dashboard.html";
    return;
  }

  const cadet = users[idx];
  const form = cadet.form || {};

  // Fill in form fields
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

  document.getElementById("remarks").value = cadet.remark || "";

  // Print form
  document.getElementById("printBtn").addEventListener("click", () => {
    window.print();
  });
})();

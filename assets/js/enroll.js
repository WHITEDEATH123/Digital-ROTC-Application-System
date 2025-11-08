// enroll.js
(function () {
  // Ensure only students can access
  const session = JSON.parse(sessionStorage.getItem("rotc_session") || "null");
  if (!session || session.role !== "student") {
    alert("Please log in as a student.");
    window.location.href = "login.html";
  }

  // Load student user record
  const users = JSON.parse(localStorage.getItem("rotc_users") || "[]");
  const me = users.find((u) => u.role === "student" && u.id === session.id);
  if (!me) {
    alert("Student not found");
    window.location.href = "login.html";
  }

  const form = document.getElementById("enrollForm");
  const photoInput = document.getElementById("photo");
  const preview = document.getElementById("photoPreview");

  function readFileAsBase64(file) {
    return new Promise((res, rej) => {
      const fr = new FileReader();
      fr.onload = () => res(fr.result);
      fr.onerror = rej;
      fr.readAsDataURL(file);
    });
  }

  if (photoInput) {
    photoInput.addEventListener("change", async (e) => {
      const f = e.target.files[0];
      if (!f) return;
      const base = await readFileAsBase64(f);
      if (preview) {
        preview.innerHTML = `<img src="${base}" alt="2x2" style="max-width:100%;max-height:100%;">`;
      }
    });
  }

  // ✅ If cadet already submitted, disable all fields except photo
  window.addEventListener("DOMContentLoaded", () => {
    if (me.form) {
      const inputs = form.querySelectorAll("input, select, textarea");
      inputs.forEach((inp) => {
        if (inp.id !== "photo") {
          inp.setAttribute("disabled", "true");
        }
      });
    }
  });

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    // Collect all fields
    const firstName = document.getElementById("fname").value.trim();
    const middleName = document.getElementById("mname").value.trim();
    const lastName = document.getElementById("lname").value.trim();
    const age = document.getElementById("age").value.trim();
    const religion = document.getElementById("religion").value.trim();
    const dob = document.getElementById("dob").value.trim();
    const pob = document.getElementById("pob").value.trim();
    const height = document.getElementById("height").value.trim();
    const weight = document.getElementById("weight").value.trim();
    const complexion = document.getElementById("complexion").value.trim();
    const bloodtype = document.getElementById("bloodtype").value.trim();
    const block = document.getElementById("block").value.trim();
    const course = document.getElementById("course").value.trim();
    const armyRotc = document.getElementById("army").value.trim();
    const address = document.getElementById("address").value.trim();
    const phone = document.getElementById("phone").value.trim();
    const email = document.getElementById("email").value.trim();
    const father = document.getElementById("father").value.trim();
    const foccupation = document.getElementById("foccupation").value.trim();
    const mother = document.getElementById("mother").value.trim();
    const moccupation = document.getElementById("moccupation").value.trim();
    const emergencyPerson = document.getElementById("emergencyPerson").value.trim();
    const relationship = document.getElementById("relationship").value.trim();
    const emergencyContact = document.getElementById("emergencyContact").value.trim();
    const ms = document.getElementById("ms").value.trim();
    const semester = document.getElementById("semester").value.trim();
    const schoolyear = document.getElementById("schoolyear").value.trim();
    const grade = document.getElementById("grade").value.trim();
    const remarks = document.getElementById("remarks").value.trim();
    const advanceCourse = document.getElementById("advanceCourse").checked;

    let photoData = me.form && me.form.photo ? me.form.photo : "";
    if (photoInput.files && photoInput.files[0]) {
      try {
        photoData = await readFileAsBase64(photoInput.files[0]);
      } catch (err) {
        console.error("Photo upload error:", err);
      }
    }

    // ✅ If resubmitting → only photo is required
    if (me.form) {
      if (!photoData) {
        alert("⚠️ Please upload a new photo before resubmitting.");
        return;
      }

      // Update only photo and status
      me.form.photo = photoData;
      me.status = "resubmitted";
      me.resubmissionTime = new Date().toISOString();
    } else {
      // ✅ First submission: validate all fields
      if (
        !firstName || !middleName || !lastName || !age || !dob || !pob ||
        !height || !weight || !block || !course || !armyRotc || !address ||
        !phone || !email || !emergencyPerson || !relationship ||
        !emergencyContact || !photoData
      ) {
        alert("⚠️ Please complete all required fields before submitting.");
        return;
      }

      // Save full form
      me.form = {
        firstName, middleName, lastName, age, religion, dob, pob,
        height, weight, complexion, bloodtype, block, course, armyRotc,
        address, phone, email, father, foccupation, mother, moccupation,
        emergencyPerson, relationship, emergencyContact, ms, semester,
        schoolyear, grade, remarks, advanceCourse, photo: photoData,
      };

      me.status = "submitted";
      me.submissionTime = new Date().toISOString();
    }

    // Always clear admin remarks when resubmitting
    me.remark = "";

    // Persist
    const all = JSON.parse(localStorage.getItem("rotc_users") || "[]");
    const idx = all.findIndex((u) => u.role === "student" && u.id === me.id);
    if (idx >= 0) all[idx] = me;
    localStorage.setItem("rotc_users", JSON.stringify(all));

    alert("Form submitted successfully!");
    window.location.href = "dashboard.html";
  });
})();

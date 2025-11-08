
    // Dashboard JS logic
    const cadetIndex = sessionStorage.getItem("selectedCadet");
    const users = JSON.parse(localStorage.getItem("rotc_users") || "[]");
    const cadet = users[cadetIndex];

    if (cadet) {
      // Display cadet name
      document.getElementById("cadetName").textContent = cadet.form?.firstName + " " + cadet.form?.lastName || "Cadet";

      // Enrollment status
      const statusBox = document.getElementById("statusBox");
      const remarksBox = document.getElementById("remarksBox");
      const remarkText = document.getElementById("remarkText");
      const printBtn = document.getElementById("printBtn");

      switch (cadet.status) {
        case "approved":
          document.getElementById("statusTitle").textContent = "Enrollment Approved";
          document.getElementById("statusDesc").textContent = "Your enrollment has been approved by the admin.";
          printBtn.style.display = "inline-block"; // show print button
          break;
        case "needs_revision":
          document.getElementById("statusTitle").textContent = "Revision Needed";
          document.getElementById("statusDesc").textContent = "Please review admin remarks and resubmit your form.";
          remarksBox.style.display = "block";
          remarkText.textContent = cadet.remark || "No remarks provided.";
          break;
        case "rejected":
          document.getElementById("statusTitle").textContent = "Enrollment Rejected";
          document.getElementById("statusDesc").textContent = "Your enrollment has been rejected by the admin.";
          remarksBox.style.display = "block";
          remarkText.textContent = cadet.remark || "No remarks provided.";
          break;
        default:
          // Not yet enrolled
          document.getElementById("statusTitle").textContent = "Not Yet Enrolled";
          document.getElementById("statusDesc").textContent = "You have not started enrollment yet.";
      }

      // Print function
      printBtn.onclick = () => {
        const form = cadet.form || {};
        let printContent = `<h2>Cadet Enrollment Form</h2>`;
        for (const key in form) {
          if (key === "advanceCourse") {
            printContent += `<p>${key}: ${form[key] ? "Yes" : "No"}</p>`;
          } else if (key === "photo" && form[key]) {
            printContent += `<p>${key}: <img src="${form[key]}" style="max-width:120px;max-height:120px;"></p>`;
          } else {
            printContent += `<p>${key}: ${form[key] || ""}</p>`;
          }
        }

        const newWindow = window.open('', '', 'width=900,height=700');
        newWindow.document.write('<html><head><title>Enrollment Form</title></head><body>');
        newWindow.document.write(printContent);
        newWindow.document.write('</body></html>');
        newWindow.document.close();
        newWindow.focus();
        newWindow.print();
        newWindow.close();
      };
    }
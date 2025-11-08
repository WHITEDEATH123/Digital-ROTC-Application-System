(function () {
  // ✅ Ensure admin session
  const session = JSON.parse(sessionStorage.getItem("rotc_session") || "null");
  if (!session || session.role !== "admin") {
    alert("Please log in as Admin.");
    window.location.href = "login.html";
  }

  // ✅ DOM elements
  const logoutBtn = document.getElementById("logoutBtn");
  const pendingTable = document.querySelector("#pendingTable tbody");
  const approvedTable = document.querySelector("#approvedTable tbody");

  // ✅ Get stored users
  let users = JSON.parse(localStorage.getItem("rotc_users") || "[]");

  // ✅ Render tables
  function renderTables() {
    pendingTable.innerHTML = "";
    approvedTable.innerHTML = "";

    users.forEach((u, idx) => {
      if (u.role !== "student" || !u.form) return;

      // Basic info row
      const row = document.createElement("tr");
      row.innerHTML = `
        <td>${u.form.firstName || ""} ${u.form.lastName || ""}</td>
        <td>${u.form.block || ""}</td>
        <td>${u.form.course || ""}</td>
        <td>${u.form.armyRotc || ""}</td>
      `;

      // Pending or needs revision → goes to Pending Table
      if (["submitted", "pending", "needs_revision"].includes(u.status)) {
        const actionTd = document.createElement("td");

        // View details button
        const viewBtn = document.createElement("button");
        viewBtn.textContent = "View Details";
        viewBtn.className = "view-btn";

        // Store cadet index & go to details page
        viewBtn.onclick = () => {
          sessionStorage.setItem("selectedCadet", idx);
          window.location.href = "view.html"; // Admin will see credentials + photo here
        };

        actionTd.appendChild(viewBtn);
        row.appendChild(actionTd);
        pendingTable.appendChild(row);
      }

      // Approved → goes to Approved Table
      if (u.status === "approved") {
        approvedTable.appendChild(row);
      }
    });
  }

  // ✅ Logout
  logoutBtn.addEventListener("click", () => {
    sessionStorage.removeItem("rotc_session");
    window.location.href = "login.html";
  });

  // ✅ Initial render
  renderTables();
})();

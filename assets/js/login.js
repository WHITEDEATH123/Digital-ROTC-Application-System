// login.js
(function () {
  const loginForm = document.getElementById("loginForm");

  const ADMIN_CREDENTIALS = { username: "admin", password: "admin123" };

  function getUsers() {
    return JSON.parse(localStorage.getItem("rotc_users") || "[]");
  }

  function saveUsers(users) {
    localStorage.setItem("rotc_users", JSON.stringify(users));
  }

  // Ensure default admin exists
  (function initAdmin() {
    let users = getUsers();
    const hasAdmin = users.some(u => u.role === "admin" && u.id === ADMIN_CREDENTIALS.username);
    if (!hasAdmin) {
      users.push({
        id: ADMIN_CREDENTIALS.username,
        password: ADMIN_CREDENTIALS.password,
        role: "admin",
        profile: { name: "Administrator", email: "" }
      });
      saveUsers(users);
    }
  })();

  loginForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const username = document.getElementById("loginUser").value.trim();
    const password = document.getElementById("loginPass").value;

    let users = getUsers();

    // Check admin
    if (username === ADMIN_CREDENTIALS.username && password === ADMIN_CREDENTIALS.password) {
      sessionStorage.setItem("rotc_session", JSON.stringify({ id: username, role: "admin" }));
      window.location.href = "admin.html";
      return;
    }

    // Check if student exists
    let student = users.find(u => u.role === "student" && u.id === username);

    if (!student) {
      // Auto-create new student
      student = {
        id: username,
        password: password,
        role: "student",
        profile: { name: "", surname: "", age: "", block: "", email: "" },
        form: null,
        status: "not_enrolled",
        remark: ""
      };
      users.push(student);
      saveUsers(users);
      alert("New student account created locally.");
    } else {
      if (student.password !== password) {
        alert("Incorrect password.");
        return;
      }
    }

    sessionStorage.setItem("rotc_session", JSON.stringify({ id: username, role: "student" }));
    window.location.href = "dashboard.html";
  });
})();

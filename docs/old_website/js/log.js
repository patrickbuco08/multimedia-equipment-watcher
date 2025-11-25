document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("loginForm");
  const signupLink = document.getElementById("signupLink");

  // ==========================================================
  // ENSURE DEFAULT ADMIN ACCOUNT EXISTS
  // ==========================================================
  const adminAccount = {
    name: "System Administrator",
    email: "admin@olivarezcollegetagaytay.edu.ph",
    password: "admin123",
    role: "admin",
  };

  const dummyAccounts = JSON.parse(localStorage.getItem("dummyAccounts")) || [];
  const adminExists = dummyAccounts.some(
    (acc) => acc.email === adminAccount.email
  );

  if (!adminExists) {
    dummyAccounts.push(adminAccount);
    localStorage.setItem("dummyAccounts", JSON.stringify(dummyAccounts));
    console.log("✅ Default admin account added!");
  }

  // ==========================================================
  // LOGIN HANDLER
  // ==========================================================
  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const email = document.getElementById("octgmail").value.trim();
    const password = document.getElementById("password").value.trim();

    // Combine all accounts
    const storedUser = JSON.parse(localStorage.getItem("userData"));
    const allAccounts = [...dummyAccounts];
    if (storedUser) allAccounts.push(storedUser);

    // Find user
    const foundAccount = allAccounts.find(
      (acc) => acc.email === email && acc.password === password
    );

    // ==========================================================
    // INVALID CREDENTIALS HANDLING
    // ==========================================================
    if (!foundAccount) {
      showPopup("❌ Invalid credentials", "Please try again.");
      showInlineError("Please try again.");
      return;
    }

    // ==========================================================
    // LOADING OVERLAY (SUCCESS)
    // ==========================================================
    const overlay = document.createElement("div");
    overlay.id = "loadingOverlay";
    Object.assign(overlay.style, {
      position: "fixed",
      top: "0",
      left: "0",
      width: "100%",
      height: "100%",
      background: "rgba(0, 0, 0, 0.85)",
      display: "flex",
      flexDirection: "column",
      alignItems: "center",
      justifyContent: "center",
      zIndex: "9999",
      color: "#fff",
      fontFamily: "Poppins, sans-serif",
    });

    const spinner = document.createElement("div");
    Object.assign(spinner.style, {
      width: "60px",
      height: "60px",
      border: "6px solid rgba(255,255,255,0.3)",
      borderTop: "6px solid #22c55e",
      borderRadius: "50%",
      animation: "spin 1s linear infinite",
    });

    const message = document.createElement("p");
    message.textContent = `🔓 Logging in... Welcome, ${foundAccount.name || "User"}!`;
    Object.assign(message.style, {
      marginTop: "20px",
      fontSize: "18px",
    });

    const style = document.createElement("style");
    style.innerHTML = `
      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
    `;
    document.head.appendChild(style);

    overlay.appendChild(spinner);
    overlay.appendChild(message);
    document.body.appendChild(overlay);

    // ==========================================================
    // SAVE SESSION INFO
    // ==========================================================
    localStorage.setItem("currentUser", JSON.stringify(foundAccount));

    // ==========================================================
    // REDIRECT BASED ON ROLE
    // ==========================================================
    setTimeout(() => {
      overlay.remove();

      if (foundAccount.role === "admin") {
        // ✅ Mark admin as logged in
        localStorage.setItem("adminLoggedIn", "true");
        window.location.href = "/html/admin.html";
      } else {
        window.location.href = "/html/monitor.html";
      }
    }, 2000);
  });

  // ==========================================================
  // SIGN UP LINK
  // ==========================================================
  signupLink.addEventListener("click", function (e) {
    e.preventDefault();
    window.location.href = "/html/sign.html";
  });

  // ==========================================================
  // POPUP FUNCTION (ERROR MESSAGE)
  // ==========================================================
  function showPopup(title, message) {
    const existing = document.getElementById("errorPopup");
    if (existing) existing.remove();

    const popup = document.createElement("div");
    popup.id = "errorPopup";
    popup.innerHTML = `
      <div class="popup-box">
        <h2>${title}</h2>
        <p>${message}</p>
        <button id="closePopup">OK</button>
      </div>
    `;

    Object.assign(popup.style, {
      position: "fixed",
      top: "0",
      left: "0",
      width: "100%",
      height: "100%",
      background: "rgba(0,0,0,0.6)",
      display: "flex",
      alignItems: "center",
      justifyContent: "center",
      zIndex: "9999",
    });

    const box = popup.querySelector(".popup-box");
    Object.assign(box.style, {
      background: "#fff",
      padding: "25px 35px",
      borderRadius: "12px",
      textAlign: "center",
      boxShadow: "0 5px 20px rgba(0,0,0,0.3)",
      animation: "fadeIn 0.3s ease",
      minWidth: "250px",
    });

    box.querySelector("h2").style.color = "#dc2626";
    box.querySelector("h2").style.marginBottom = "8px";
    box.querySelector("p").style.color = "#333";

    const button = box.querySelector("#closePopup");
    Object.assign(button.style, {
      marginTop: "15px",
      padding: "8px 20px",
      border: "none",
      background: "#dc2626",
      color: "white",
      borderRadius: "6px",
      cursor: "pointer",
      transition: "0.3s",
    });
    button.addEventListener("click", () => popup.remove());
    button.addEventListener("mouseover", () => (button.style.background = "#b91c1c"));
    button.addEventListener("mouseout", () => (button.style.background = "#dc2626"));

    document.body.appendChild(popup);
  }

  // ==========================================================
  // INLINE ERROR BELOW LOGIN BUTTON
  // ==========================================================
  function showInlineError(text) {
    let errorDiv = document.getElementById("inlineError");
    if (!errorDiv) {
      errorDiv = document.createElement("p");
      errorDiv.id = "inlineError";
      form.appendChild(errorDiv);
    }
    errorDiv.textContent = text;
    Object.assign(errorDiv.style, {
      color: "#dc2626",
      textAlign: "center",
      marginTop: "10px",
      fontWeight: "500",
    });
  }
});

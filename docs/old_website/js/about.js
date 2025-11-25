document.addEventListener("DOMContentLoaded", () => {
  const logoutBtn = document.getElementById("logoutBtn");
  if (!logoutBtn) return;

  logoutBtn.addEventListener("click", () => {
    // Create overlay
    const overlay = document.createElement("div");
    Object.assign(overlay.style, {
      position: "fixed",
      top: "0",
      left: "0",
      width: "100%",
      height: "100%",
      background: "rgba(255, 255, 255, 0.95)",
      display: "flex",
      flexDirection: "column",
      justifyContent: "center",
      alignItems: "center",
      zIndex: "9999",
      fontFamily: "Poppins, sans-serif",
      transition: "opacity 0.4s ease"
    });

    // Spinner
    const spinner = document.createElement("div");
    Object.assign(spinner.style, {
      width: "60px",
      height: "60px",
      border: "6px solid #ccc",
      borderTop: "6px solid #b91c1c",
      borderRadius: "50%",
      animation: "spin 1s linear infinite"
    });

    // Message
    const msg = document.createElement("p");
    msg.textContent = "🔴 Logging out... Please wait";
    Object.assign(msg.style, {
      marginTop: "20px",
      fontSize: "18px",
      color: "#333"
    });

    // Add spinner animation
    const style = document.createElement("style");
    style.textContent = `
      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
    `;
    document.head.appendChild(style);

    // Append elements
    overlay.append(spinner, msg);
    document.body.appendChild(overlay);

    // Simulate logout process
    setTimeout(() => {
      localStorage.removeItem("isLoggedIn");
      overlay.innerHTML = `
        <p style="font-size:20px; color:#14532d; font-weight:600;">
          ✅ Logged out successfully!
        </p>
      `;

      // Redirect to login page
      setTimeout(() => {
        window.location.href = "/html/log.html"; // ✅ Corrected path
      }, 1500);
    }, 1500);
  });
});

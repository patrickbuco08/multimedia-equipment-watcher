document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("signupForm");
  const loginLink = document.getElementById("loginLink");

  // ✅ Handle User Sign-Up
  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const name = document.getElementById("name").value.trim();
    const email = document.getElementById("octgmail").value.trim();
    const phone = document.getElementById("mobilenumber").value.trim();
    const password = document.getElementById("password").value.trim();
    const confirmPassword = document.getElementById("confirmPassword").value.trim();

    if (!name || !email || !phone || !password || !confirmPassword) {
      alert("⚠️ Please fill in all fields.");
      return;
    }

    if (password !== confirmPassword) {
      alert("❌ Passwords do not match.");
      return;
    }

    // ✅ Prevent duplicate user accounts
    const storedUser = JSON.parse(localStorage.getItem("userData"));
    if (storedUser && (storedUser.email === email || storedUser.phone === phone)) {
      alert("⚠️ This email or phone number is already registered.");
      return;
    }

    // ✅ Save new user account to localStorage
    const userData = { name, email, phone, password, role: "user" };
    localStorage.setItem("userData", JSON.stringify(userData));

    // ✅ Show success animation
    showLoadingAnimation("✅ Sign-Up Successful! Redirecting to Login...");
  });

  // ✅ Go to Login page
  loginLink.addEventListener("click", (e) => {
    e.preventDefault();
    window.location.href = "log.html";
  });

  // ✅ Loading animation
  function showLoadingAnimation(message) {
    const overlay = document.createElement("div");
    Object.assign(overlay.style, {
      position: "fixed",
      top: "0",
      left: "0",
      width: "100%",
      height: "100%",
      background: "rgba(0, 0, 0, 0.8)",
      display: "flex",
      flexDirection: "column",
      alignItems: "center",
      justifyContent: "center",
      zIndex: "9999",
      color: "#fff",
      fontFamily: "Poppins, sans-serif"
    });

    const loader = document.createElement("div");
    Object.assign(loader.style, {
      border: "6px solid rgba(255,255,255,0.3)",
      borderTop: "6px solid #22c55e",
      borderRadius: "50%",
      width: "60px",
      height: "60px",
      animation: "spin 1s linear infinite",
    });

    const text = document.createElement("p");
    text.textContent = message;
    Object.assign(text.style, {
      marginTop: "20px",
      fontSize: "18px"
    });

    const style = document.createElement("style");
    style.textContent = `
      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
    `;
    document.head.appendChild(style);

    overlay.appendChild(loader);
    overlay.appendChild(text);
    document.body.appendChild(overlay);

    setTimeout(() => {
      overlay.remove();
      window.location.href = "log.html"; // ✅ Redirect to login after success
    }, 2000);
  }
});

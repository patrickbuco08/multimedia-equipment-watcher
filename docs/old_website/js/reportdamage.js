/* ==========================================================
   REPORTDAMAGE.JS – FINAL VERSION (WITH FIXED DROPDOWN)
   Features:
   ✔ Image preview with validation
   ✔ Smooth SUBMIT LOADING → SUCCESS animation
   ✔ Sends report to admin.js (if open)
   ✔ Saves to localStorage if admin not active
   ✔ Logout → log.html
   ✔ Reports ▼ dropdown click FIXED
========================================================== */

document.addEventListener("DOMContentLoaded", () => {
  const $ = (s) => document.querySelector(s);

  const imageInput = $("#damagePhoto");
  const imagePreview = $("#imagePreview");
  const imageBox = $("#imageBox");
  const form = $("#damageForm");
  const submitBtn = $("#submitBtn");
  const logoutBtn = $(".logout-btn");

  /* ==========================================================
     FIXED DROPDOWN (Reports ▼)
  ========================================================== */
  const dropBtn = document.querySelector(".dropbtn");
  const dropContent = document.querySelector(".dropdown-content");

  if (dropBtn && dropContent) {
    dropBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      dropContent.classList.toggle("show");
    });

    // close dropdown when clicking outside
    document.addEventListener("click", () => {
      dropContent.classList.remove("show");
    });
  }

  /* ==========================================================
     CLICK BOX → OPEN FILE SELECT
  ========================================================== */
  if (imageBox && imageInput) {
    imageBox.addEventListener("click", () => imageInput.click());
  }

  /* ==========================================================
     IMAGE PREVIEW
  ========================================================== */
  if (imageInput) {
    imageInput.addEventListener("change", () => {
      const file = imageInput.files[0];

      if (!file) {
        imagePreview.innerHTML = `<span style="color:#6b7280;">No image selected</span>`;
        return;
      }

      if (!file.type.startsWith("image/")) {
        alert("⚠ Please upload a valid image file.");
        imageInput.value = "";
        return;
      }

      const fr = new FileReader();
      fr.onload = () => {
        imagePreview.innerHTML = `
          <img src="${fr.result}" 
               style="max-width:220px;border-radius:6px;margin-top:8px;">
        `;
      };
      fr.readAsDataURL(file);
    });
  }

  /* ==========================================================
     FORM SUBMIT LOGIC
  ========================================================== */
  if (form) {
    form.addEventListener("submit", (e) => {
      e.preventDefault();

      const item = $("#item").value.trim();
      const date = $("#date").value;
      const quantity = $("#quantity").value;
      const description = $("#description").value.trim();

      if (!item || !date || !quantity || !description) {
        alert("⚠ Please fill out all required fields.");
        return;
      }

      // LOADING ANIMATION
      submitBtn.disabled = true;
      submitBtn.innerText = "Submitting...";

      const file = imageInput.files[0];

      if (file) {
        const reader = new FileReader();
        reader.onload = () => {
          sendReport({
            item,
            date,
            quantity,
            description,
            image: reader.result,
          });
        };
        reader.readAsDataURL(file);
      } else {
        sendReport({
          item,
          date,
          quantity,
          description,
          image: null,
        });
      }
    });
  }

  /* ==========================================================
     SEND REPORT (To admin.js OR local storage)
  ========================================================== */
  function sendReport(data) {
    const report = {
      id: Date.now(),
      type: "damage",
      item: data.item,
      date: data.date,
      quantity: data.quantity,
      desc: data.description,
      image: data.image,
      status: "pending",
    };

    if (typeof window.submitDamageReport === "function") {
      try {
        window.submitDamageReport(report);
      } catch (e) {
        saveDirect(report);
      }
    } else {
      saveDirect(report);
    }

    // SUCCESS MESSAGE
    setTimeout(() => {
      submitBtn.innerText = "Submitted ✔";
      submitBtn.style.background = "#16a34a";

      setTimeout(() => {
        submitBtn.disabled = false;
        submitBtn.innerText = "Submit Report";
        submitBtn.style.background = "";

        form.reset();
        imagePreview.innerHTML = `<span style="color:#6b7280;">No image selected</span>`;
      }, 1500);
    }, 800);

    localStorage.setItem("reports_last_update", String(Date.now()));
  }

  /* ==========================================================
     SAVE TO LOCALSTORAGE (Fallback)
  ========================================================== */
  function saveDirect(report) {
    const r = JSON.parse(localStorage.getItem("reports")) || [];
    r.push(report);
    localStorage.setItem("reports", JSON.stringify(r));
  }

  /* ==========================================================
     LOGOUT → GO TO log.html
  ========================================================== */
  if (logoutBtn) {
    logoutBtn.addEventListener("click", () => {
      alert("Logged out.");
      window.location.href = "/log.html";
    });
  }
});

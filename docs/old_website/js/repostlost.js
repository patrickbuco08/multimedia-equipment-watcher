/* ==========================================================
   REPORT LOST JS — CONNECTED TO ADMIN
   ========================================================== */

// Dropdown toggle
const dropbtn = document.querySelector(".dropbtn");
const dropdownContent = document.querySelector(".dropdown-content");

if (dropbtn) {
  dropbtn.addEventListener("click", (e) => {
    e.stopPropagation();
    dropdownContent.classList.toggle("show");
  });
}

window.addEventListener("click", () => {
  if (dropdownContent) dropdownContent.classList.remove("show");
});

// Image upload preview
const lostImage = document.getElementById("lostImage");
const imageBox = document.getElementById("imageBox");
const imagePreview = document.getElementById("imagePreview");

imageBox.onclick = () => lostImage.click();

lostImage.addEventListener("change", function () {
  const file = this.files[0];

  if (!file) {
    imagePreview.innerHTML = "<span>No image selected</span>";
    return;
  }

  const reader = new FileReader();
  reader.onload = (e) => {
    imagePreview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
  };

  reader.readAsDataURL(file);
});

// Form submit
document.getElementById("lostForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const item = document.getElementById("itemName").value.trim();
  const date = document.getElementById("lostDate").value.trim();
  const qty = document.getElementById("quantity").value.trim();
  const desc = document.getElementById("description").value.trim();

  if (!item || !date || !qty || !desc) {
    alert("⚠️ Please complete all fields");
    return;
  }

  showLoading("Submitting...");

  setTimeout(() => {
    hideLoading();

    showSuccess("✔ Lost item report submitted!");

    /* ───────────────────────────────────────────────
       SEND REPORT TO ADMIN.JS
       ─────────────────────────────────────────────── */
    if (window.submitLostReport) {
      window.submitLostReport({
        item,
        date,
        qty,
        desc,
        image: lostImage.files[0]
          ? imagePreview.querySelector("img").src
          : null,
      });
    }

    document.getElementById("lostForm").reset();
    imagePreview.innerHTML = "<span>No image selected</span>";

  }, 1500);
});

// Loading animation
function showLoading(text) {
  const loader = document.createElement("div");
  loader.id = "loadingScreen";
  loader.innerHTML = `<div class="loader"></div><p>${text}</p>`;
  document.body.appendChild(loader);

  setTimeout(() => loader.classList.add("show"), 10);
}

function hideLoading() {
  const loader = document.getElementById("loadingScreen");
  if (loader) loader.remove();
}

// Success message
function showSuccess(msg) {
  const box = document.createElement("div");
  box.id = "successMessage";

  box.innerHTML = `
    <div class="success-content">
      <span class="checkmark">✔</span>
      <p>${msg}</p>
    </div>
  `;

  document.body.appendChild(box);

  setTimeout(() => box.classList.add("show"), 50);

  setTimeout(() => {
    box.classList.remove("show");
    setTimeout(() => box.remove(), 500);
  }, 2000);
}

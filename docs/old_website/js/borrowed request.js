// ==========================================================
// INVENTORY SYSTEM (FULL)
// ==========================================================

const STORAGE_KEY = "inventory-data";

const DEFAULT_INVENTORY = [
  { name: "Microphone", available: 12, unavailable: 0 },
  { name: "Speaker", available: 10, unavailable: 0 },
  { name: "Projector", available: 5, unavailable: 0 },
  { name: "HDMI Cable", available: 20, unavailable: 0 },
  { name: "Camera", available: 10, unavailable: 0 },
  { name: "Tripod", available: 8, unavailable: 0 },
  { name: "Laptop", available: 15, unavailable: 0 },
];

// Load or create inventory
function loadInventory() {
  const data = localStorage.getItem(STORAGE_KEY);
  if (!data) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(DEFAULT_INVENTORY));
    return JSON.parse(JSON.stringify(DEFAULT_INVENTORY));
  }
  return JSON.parse(data);
}

// Save inventory
function saveInventory(inv) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(inv));
}

// Find item
function findItem(inv, name) {
  return inv.find(i => i.name.toLowerCase() === name.toLowerCase());
}

// Borrow item
function borrowItem(name, qty) {
  const inv = loadInventory();
  const item = findItem(inv, name);
  qty = Number(qty);

  if (!item) return { success: false, message: "Item not found" };
  if (item.available < qty) return { success: false, message: "Not enough stock" };

  item.available -= qty;
  item.unavailable += qty;

  saveInventory(inv);
  updateInventoryTable();

  return { success: true };
}

// Update table if exists
function updateInventoryTable() {
  const inv = loadInventory();
  const rows = document.querySelectorAll("tbody tr");

  rows.forEach(row => {
    const name = row.dataset.item;
    const item = findItem(inv, name);
    if (!item) return;

    const availableCell = row.querySelector(".available");
    const unavailableCell = row.querySelector(".unavailable");

    if (availableCell) availableCell.textContent = item.available;
    if (unavailableCell) unavailableCell.textContent = item.unavailable;
  });
}

updateInventoryTable();

// ==========================================================
// BORROW FORM
// ==========================================================

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("borrowForm");
  const statusDiv = document.getElementById("status");
  const submitBtn = form.querySelector(".submit-btn");

  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const date = document.getElementById("borrowDate").value;
    const borrowTime = document.getElementById("borrowTime").value;
    const returnTime = document.getElementById("returnTime").value;
    const item = document.getElementById("itemSelect").value;
    const quantity = parseInt(document.getElementById("quantity").value);

    if (!date || !borrowTime || !returnTime || !item || !quantity) {
      showStatus("⚠️ Please fill out all fields.", "error");
      return;
    }

    // INVENTORY UPDATE HERE
    const invResult = borrowItem(item, quantity);

    if (!invResult.success) {
      showStatus(`❌ ${invResult.message}`, "error");
      return;
    }

    startLoading(submitBtn);

    setTimeout(() => {
      saveBorrowRequest({
        id: Date.now(),
        date,
        borrowTime,
        returnTime,
        item,
        quantity,
        status: "Pending",
      });

      addNotification(`Borrow request submitted: ${quantity} × ${item}`);

      form.reset();
      finishSuccess(submitBtn);
      showSuccessModal();
      openNotificationDropdown();

    }, 1200);
  });

  function showStatus(message, type) {
    statusDiv.className = `status-message ${type}`;
    statusDiv.innerHTML = message;
    statusDiv.style.display = "block";
  }

  function saveBorrowRequest(data) {
    let list = JSON.parse(localStorage.getItem("borrowRequests")) || [];
    list.push(data);
    localStorage.setItem("borrowRequests", JSON.stringify(list));
  }

  // Loading button functions
  function startLoading(btn) {
    btn.disabled = true;
    btn.classList.add("loading");
    btn.innerHTML = `<span class="loader"></span> Submitting...`;
  }

  function finishSuccess(btn) {
    btn.classList.remove("loading");
    btn.classList.add("success");
    btn.innerHTML = "Submitted ✓";
    setTimeout(() => resetButton(btn), 1500);
  }

  function resetButton(btn) {
    btn.disabled = false;
    btn.classList.remove("success");
    btn.innerHTML = "Submit";
  }

  // Success modal
  function showSuccessModal() {
    const modal = document.createElement("div");
    modal.id = "successModal";

    modal.style.cssText = `
      position: fixed; top:0; left:0;
      width:100%; height:100%;
      background: rgba(0,0,0,0.5);
      display:flex; align-items:center; justify-content:center;
      z-index:9999;
    `;

    modal.innerHTML = `
      <div style="
        background:#fff;
        padding:20px 30px;
        border-radius:10px;
        color:#16a34a;
        font-size:22px;
        font-weight:bold;
        text-align:center;
      ">
        ✓ Borrow Request Submitted!
      </div>
    `;

    document.body.appendChild(modal);

    setTimeout(() => modal.remove(), 1300);
  }
});

// ==========================================================
// NOTIFICATION SYSTEM
// ==========================================================

const notifBtn = document.getElementById("notifButton");
const notifDropdown = document.getElementById("notifDropdown");
const notifCount = document.getElementById("notifCount");
const notifList = notifDropdown?.querySelector("ul");
const clearNotif = document.getElementById("clearNotif");

if (notifBtn) {
  notifBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    notifBtn.classList.toggle("active");
  });

  document.addEventListener("click", () => notifBtn.classList.remove("active"));
}

function addNotification(msg) {
  if (!notifList) return;

  const li = document.createElement("li");
  li.textContent = msg;
  notifList.prepend(li);

  let count = parseInt(notifCount.textContent) || 0;
  notifCount.textContent = count + 1;
  notifCount.style.display = "inline-block";
}

if (clearNotif) {
  clearNotif.onclick = () => {
    notifList.innerHTML = "<li>No new notifications.</li>";
    notifCount.textContent = "0";
    notifCount.style.display = "none";
  };
}

function openNotificationDropdown() {
  notifBtn.classList.add("active");
}

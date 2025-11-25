/* ==========================================================
   ADMIN.JS – FINAL VERSION WITH AUTO UPDATE + SUBMITTED VIEW
========================================================== */

document.addEventListener("DOMContentLoaded", () => {
  // 🔄 AUTO UPDATE WHEN BORROW PAGE SUBMITS
  if (localStorage.getItem("refreshAdmin") === "1") {
    renderAllSections();
    renderLostReports();
    renderDamageReports();
    renderReturnedItems();
    renderSubmittedRequests();
    localStorage.removeItem("refreshAdmin");
  }

  renderAllSections();
  renderLostReports();
  renderDamageReports();
  renderReturnedItems();
  renderSubmittedRequests();
  setupSidebar();
});

/* ==========================================================
   STORAGE FUNCTIONS
========================================================== */

function loadRequests() {
  return JSON.parse(localStorage.getItem("borrowRequests")) || [];
}

function saveRequests(list) {
  localStorage.setItem("borrowRequests", JSON.stringify(list));
}

function loadReports() {
  return JSON.parse(localStorage.getItem("reports")) || [];
}

function saveReports(list) {
  localStorage.setItem("reports", JSON.stringify(list));
}

/* ==========================================================
   MAIN RENDER
========================================================== */

function renderAllSections() {
  renderPendingRequests();
  renderApprovedRequests();
  renderDeclinedRequests();
}

/* ==========================================================
   🟡 PENDING BORROW REQUESTS
========================================================== */

function renderPendingRequests() {
  const container = document.getElementById("pending");
  if (!container) return;

  const all = loadRequests();
  const pending = all.filter((r) => r.status === "Pending");

  container.innerHTML = `
    <h2>Pending Requests</h2>
    <div id="pending-list">
      ${pending.length === 0 ? "<p>No pending requests.</p>" : ""}
    </div>
  `;

  const wrapper = document.getElementById("pending-list");

  pending.forEach((r) => {
    const div = document.createElement("div");
    div.className = "request-box pending";

    div.innerHTML = `
      <p><strong>Date:</strong> ${r.date}</p>
      <p><strong>Borrow Time:</strong> ${r.borrowTime}</p>
      <p><strong>Return Time:</strong> ${r.returnTime}</p>
      <p><strong>Item:</strong> ${r.item}</p>
      <p><strong>Quantity:</strong> ${r.quantity}</p>

      <div class="actions">
        <button class="approve-btn" onclick="approveRequest(${r.id})">Approve</button>
        <button class="decline-btn" onclick="declineRequest(${r.id})">Decline</button>
      </div>
    `;

    wrapper.appendChild(div);
  });
}

/* ==========================================================
   🟢 APPROVED REQUESTS
========================================================== */

function renderApprovedRequests() {
  const container = document.getElementById("borrowed");
  if (!container) return;

  const all = loadRequests();
  const list = all.filter((r) => r.status === "Approved");

  container.innerHTML = `
    <h2>Borrowed Items</h2>
    <div id="borrowed-list">
      ${list.length === 0 ? "<p>No borrowed items.</p>" : ""}
    </div>
  `;

  const wrapper = document.getElementById("borrowed-list");

  list.forEach((r) => {
    const div = document.createElement("div");
    div.className = "request-box approved";

    div.innerHTML = `
      <p><strong>Date:</strong> ${r.date}</p>
      <p><strong>Time:</strong> ${r.borrowTime} - ${r.returnTime}</p>
      <p><strong>Item:</strong> ${r.item}</p>
      <p><strong>Quantity:</strong> ${r.quantity}</p>

      <div class="actions">
        <button class="return-btn" onclick="markReturned(${r.id})">Returned</button>
      </div>

      <p class="status-label approved">APPROVED ✔</p>
    `;

    wrapper.appendChild(div);
  });
}

/* ==========================================================
   🔴 DECLINED REQUESTS
========================================================== */

function renderDeclinedRequests() {
  const container = document.getElementById("cleared");
  if (!container) return;

  const all = loadRequests();
  const list = all.filter((r) => r.status === "Declined");

  container.innerHTML = `
    <h2>Cleared Records</h2>
    <div id="cleared-list">
      ${list.length === 0 ? "<p>No declined requests.</p>" : ""}
    </div>
  `;

  const wrapper = document.getElementById("cleared-list");

  list.forEach((r) => {
    const div = document.createElement("div");
    div.className = "request-box declined";

    div.innerHTML = `
      <p><strong>Date:</strong> ${r.date}</p>
      <p><strong>Time:</strong> ${r.borrowTime} - ${r.returnTime}</p>
      <p><strong>Item:</strong> ${r.item}</p>
      <p><strong>Quantity:</strong> ${r.quantity}</p>
      <p class="status-label declined">DECLINED ✘</p>
    `;

    wrapper.appendChild(div);
  });
}

/* ==========================================================
   🟦 RETURNED ITEMS
========================================================== */

function renderReturnedItems() {
  const container = document.getElementById("returned");
  if (!container) return;

  const all = loadRequests();
  const returned = all.filter((r) => r.status === "Returned");

  container.innerHTML = `
    <h2>Returned Items</h2>
    <div id="returned-list">
      ${returned.length === 0 ? "<p>No returned items.</p>" : ""}
    </div>
  `;

  const wrapper = document.getElementById("returned-list");

  returned.forEach((r) => {
    const div = document.createElement("div");
    div.className = "request-box returned";

    div.innerHTML = `
      <p><strong>Item:</strong> ${r.item}</p>
      <p><strong>Quantity:</strong> ${r.quantity}</p>
      <p><strong>Returned Date:</strong> ${r.returnTime}</p>
      <p class="status-label returned">RETURNED ✔</p>
    `;

    wrapper.appendChild(div);
  });
}

/* ==========================================================
   ✔ ACTIONS FOR BORROW REQUESTS
========================================================== */

function approveRequest(id) {
  updateRequest(id, "Approved");
}

function declineRequest(id) {
  updateRequest(id, "Declined");
}

function markReturned(id) {
  updateRequest(id, "Returned");
}

function updateRequest(id, status) {
  const list = loadRequests();
  const index = list.findIndex((r) => r.id == id);

  if (index !== -1) {
    list[index].status = status;
    saveRequests(list);
    renderAllSections();
    renderReturnedItems();
    renderSubmittedRequests();
  }
}

/* ==========================================================
   📌 LOST REPORTS
========================================================== */

function renderLostReports() {
  const container = document.getElementById("lost");
  if (!container) return;

  const list = loadReports().filter((r) => r.type === "lost");

  container.innerHTML = `
    <h2>Reported Lost Items</h2>
    <div id="lost-list">
      ${list.length === 0 ? "<p>No lost item reports.</p>" : ""}
    </div>
  `;

  const wrapper = document.getElementById("lost-list");

  list.forEach((r) => {
    const div = document.createElement("div");
    div.className = "report-box";

    div.innerHTML = `
      <p><strong>Item:</strong> ${r.item}</p>
      <p><strong>Description:</strong> ${r.description}</p>
      <p><strong>Date:</strong> ${r.date}</p>
      ${r.image ? `<img src="${r.image}" class="report-img">` : ""}

      <div class="actions">
        <button onclick="approveLost(${r.id})" class="approve-btn">Approve</button>
        <button onclick="declineLost(${r.id})" class="decline-btn">Decline</button>
        <button onclick="deleteReport(${r.id})" class="delete-btn">Delete</button>
      </div>
    `;

    wrapper.appendChild(div);
  });
}

/* ==========================================================
   🔧 DAMAGE REPORTS
========================================================== */

function renderDamageReports() {
  const container = document.getElementById("damage");
  if (!container) return;

  const list = loadReports().filter((r) => r.type === "damage");

  container.innerHTML = `
    <h2>Reported Damaged Items</h2>
    <div id="damage-list">
      ${list.length === 0 ? "<p>No damaged item reports.</p>" : ""}
    </div>
  `;

  const wrapper = document.getElementById("damage-list");

  list.forEach((r) => {
    const div = document.createElement("div");
    div.className = "report-box";

    div.innerHTML = `
      <p><strong>Item:</strong> ${r.item}</p>
      <p><strong>Description:</strong> ${r.description}</p>
      <p><strong>Date:</strong> ${r.date}</p>
      ${r.image ? `<img src="${r.image}" class="report-img">` : ""}

      <div class="actions">
        <button onclick="approveDamage(${r.id})" class="approve-btn">Approve</button>
        <button onclick="declineDamage(${r.id})" class="decline-btn">Decline</button>
        <button onclick="deleteReport(${r.id})" class="delete-btn">Delete</button>
      </div>
    `;

    wrapper.appendChild(div);
  });
}

/* ==========================================================
   ✔ LOST & DAMAGE ACTIONS
========================================================== */

function approveLost(id) {
  updateReportStatus(id, "approved");
}

function declineLost(id) {
  updateReportStatus(id, "declined");
}

function approveDamage(id) {
  updateReportStatus(id, "approved");
}

function declineDamage(id) {
  updateReportStatus(id, "declined");
}

function updateReportStatus(id, status) {
  const list = loadReports();
  const index = list.findIndex((r) => r.id == id);

  if (index !== -1) {
    list[index].status = status;
    saveReports(list);
    renderLostReports();
    renderDamageReports();
  }
}

/* ==========================================================
   ❌ DELETE SINGLE REPORT
========================================================== */

function deleteReport(id) {
  let list = loadReports();
  list = list.filter((r) => r.id != id);
  saveReports(list);
  renderLostReports();
  renderDamageReports();
}

/* ==========================================================
   ⭐ SHOW ALL SUBMITTED REQUESTS (LOCAL STORAGE VIEW)
========================================================== */

function renderSubmittedRequests() {
  const container = document.getElementById("submitted");
  if (!container) return;

  const list = loadRequests();

  container.innerHTML = `
    <h2>All Submitted Requests</h2>
    <div id="submitted-list">
      ${list.length === 0 ? "<p>No submitted requests.</p>" : ""}
    </div>
  `;

  const wrapper = document.getElementById("submitted-list");

  list.forEach((r) => {
    const div = document.createElement("div");
    div.className = "request-box";

    div.innerHTML = `
      <p><strong>Date:</strong> ${r.date}</p>
      <p><strong>Borrow Time:</strong> ${r.borrowTime}</p>
      <p><strong>Return Time:</strong> ${r.returnTime}</p>
      <p><strong>Item:</strong> ${r.item}</p>
      <p><strong>Quantity:</strong> ${r.quantity}</p>
      <p><strong>Status:</strong> ${r.status}</p>
    `;

    wrapper.appendChild(div);
  });
}

/* ==========================================================
   SIDEBAR FUNCTIONALITY
========================================================== */

function setupSidebar() {
  const menuItems = document.querySelectorAll("#menu li");
  const sections = document.querySelectorAll(".section");
  const title = document.getElementById("section-title");

  menuItems.forEach((item) => {
    item.addEventListener("click", () => {
      menuItems.forEach((li) => li.classList.remove("active"));
      item.classList.add("active");

      const target = item.getAttribute("data-section");
      sections.forEach((sec) => sec.classList.remove("active"));

      const shown = document.getElementById(target);
      shown.classList.add("active");
      title.textContent = item.textContent;
    });
  });
}

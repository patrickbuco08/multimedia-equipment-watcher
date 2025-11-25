// ==========================================================
// inventory.js — FINAL COMPLETE MONITOR INVENTORY SYSTEM
// ==========================================================

(function () {
  const STORAGE_KEY = "inventory";

  // ==========================================================
  // Default inventory list (You can modify the numbers here)
  // ==========================================================
  const DEFAULT = [
    { name: "Camera", available: 10, unavailable: 0 },
    { name: "Tripod", available: 8, unavailable: 0 },
    { name: "Microphone", available: 12, unavailable: 0 },
    { name: "Speaker", available: 10, unavailable: 0 },
    { name: "Projector", available: 5, unavailable: 0 },
    { name: "HDMI Cable", available: 20, unavailable: 0 },
    { name: "Laptop", available: 15, unavailable: 0 }
  ];

  // ==========================================================
  // Ensure inventory exists in localStorage
  // ==========================================================
  function ensureInventory() {
    const raw = localStorage.getItem(STORAGE_KEY);

    if (!raw) {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(DEFAULT));
      return JSON.parse(JSON.stringify(DEFAULT));
    }

    try {
      return JSON.parse(raw);
    } catch (err) {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(DEFAULT));
      return JSON.parse(JSON.stringify(DEFAULT));
    }
  }

  // Save inventory data
  function saveInventory(inv) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(inv));
  }

  // Find item index
  function findIndex(inv, name) {
    return inv.findIndex(item => item.name.toLowerCase() === name.toLowerCase());
  }

  // ==========================================================
  // Borrow (Single Item)
  // ==========================================================
  function reserve(itemName, qty) {
    qty = Number(qty);
    if (qty <= 0) return false;

    const inv = ensureInventory();
    const index = findIndex(inv, itemName);

    if (index === -1) return false;

    const item = inv[index];

    if (item.available < qty) return false;

    item.available -= qty;
    item.unavailable += qty;

    saveInventory(inv);
    updateMonitorTable();
    return true;
  }

  // ==========================================================
  // Borrow (Multiple Items)
  //
  // Example:
  // Inventory.reserveMultiple([
  //   { name: "Microphone", qty: 5 },
  //   { name: "Speaker", qty: 5 },
  //   { name: "Projector", qty: 5 },
  //   { name: "HDMI Cable", qty: 5 }
  // ]);
  // ==========================================================
  function reserveMultiple(itemsArray) {
    const inv = ensureInventory();

    // Validate all first
    for (const entry of itemsArray) {
      const idx = findIndex(inv, entry.name);
      if (idx === -1) return false;
      if (inv[idx].available < entry.qty) return false;
    }

    // Apply borrow after validation
    for (const entry of itemsArray) {
      const idx = findIndex(inv, entry.name);
      inv[idx].available -= entry.qty;
      inv[idx].unavailable += entry.qty;
    }

    saveInventory(inv);
    updateMonitorTable();
    return true;
  }

  // ==========================================================
  // Return Item
  // ==========================================================
  function release(itemName, qty) {
    qty = Number(qty);
    if (qty <= 0) return false;

    const inv = ensureInventory();
    const index = findIndex(inv, itemName);

    if (index === -1) return false;

    const item = inv[index];

    qty = Math.min(qty, item.unavailable);

    item.available += qty;
    item.unavailable -= qty;

    saveInventory(inv);
    updateMonitorTable();
    return true;
  }

  // ==========================================================
  // Mark Item as Damaged
  // ==========================================================
  function markDamaged(itemName, qty) {
    qty = Number(qty);
    if (qty <= 0) return false;

    const inv = ensureInventory();
    const index = findIndex(inv, itemName);

    if (index === -1) return false;

    const item = inv[index];

    const removeQty = Math.min(qty, item.available);

    item.available -= removeQty;

    saveInventory(inv);
    updateMonitorTable();
    return true;
  }

  // ==========================================================
  // Update Inventory Table in HTML
  // ==========================================================
  function updateMonitorTable() {
    const inv = ensureInventory();
    const rows = document.querySelectorAll("tbody tr");

    rows.forEach(row => {
      const name = row.dataset.item;
      const idx = findIndex(inv, name);
      if (idx === -1) return;

      const item = inv[idx];

      const availableCell = row.querySelector(".available");
      const unavailableCell = row.querySelector(".unavailable");

      // Works for <td> or <input>
      if (availableCell) {
        if ("value" in availableCell) availableCell.value = item.available;
        else availableCell.textContent = item.available;
      }

      if (unavailableCell) {
        if ("value" in unavailableCell) unavailableCell.value = item.unavailable;
        else unavailableCell.textContent = item.unavailable;
      }
    });
  }

  // ==========================================================
  // Export All Functions
  // ==========================================================
  window.Inventory = {
    ensureInventory,
    getInventory: ensureInventory,
    saveInventory,
    reserve,
    reserveMultiple,
    release,
    markDamaged,
    updateMonitorTable
  };

  // Initialize and update UI immediately
  ensureInventory();
  updateMonitorTable();

})();

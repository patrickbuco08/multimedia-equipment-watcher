document.addEventListener("DOMContentLoaded", () => {
  // Example data from previous request (or localStorage)
  const storedData = JSON.parse(localStorage.getItem("borrowRequest")) || {
    date: "02/06/25",
    borrowTime: "8:00",
    returnTime: "11:30",
    item: "Chairs",
    quantity: "48",
  };

  // Insert values into the display
  document.getElementById("entryDate").textContent = storedData.date;
  document.getElementById("entryBorrow").textContent = storedData.borrowTime;
  document.getElementById("entryReturn").textContent = storedData.returnTime;
  document.getElementById("entryItem").textContent = storedData.item;
  document.getElementById("entryQuantity").textContent = storedData.quantity;

  // Logout button
  const logoutBtn = document.getElementById("logoutBtn");
  logoutBtn.addEventListener("click", () => {
    localStorage.removeItem("isLoggedIn");
    alert("✅ You have been logged out successfully!");
    window.location.href = "/html/log.html";
  });
});

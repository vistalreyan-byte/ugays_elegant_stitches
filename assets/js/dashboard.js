// ==========================================
// SIDEBAR TOGGLE
// ==========================================

const menuBtn = document.getElementById("menuBtn");
const sidebar = document.getElementById("sidebar");

if (menuBtn) {

    menuBtn.addEventListener("click", function () {

        sidebar.classList.toggle("open");

    });

}


// ==========================================
// SIDEBAR ACTIVE MENU
// ==========================================

const navItems =
    document.querySelectorAll(".nav-item");

navItems.forEach(function (item) {

    item.addEventListener("click", function () {

        navItems.forEach(function (nav) {

            nav.classList.remove("active");

        });

        item.classList.add("active");

    });

});


// ==========================================
// SEARCH
// ==========================================

const searchInput =
    document.getElementById("globalSearch");

if (searchInput) {

    searchInput.addEventListener("keyup", function () {

        const searchValue =
            this.value.toLowerCase();

        const rows =
            document.querySelectorAll("tbody tr");

        rows.forEach(function (row) {

            const text =
                row.textContent.toLowerCase();

            if (text.includes(searchValue)) {

                row.style.display = "";

            } else {

                row.style.display = "none";

            }

        });

    });

}


// ==========================================
// NOTIFICATION
// ==========================================

const notification =
    document.querySelector(".notification-btn");

if (notification) {

    notification.addEventListener("click", function () {

        alert(
            "You have 3 new notifications."
        );

    });

}
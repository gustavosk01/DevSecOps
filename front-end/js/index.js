// MOBILE MENU
let mm_state = false;
function toggleMenu() {
    if (!mm_state) {
        document.querySelector(".menu-btn").classList.add("open");
        document.querySelector(".mobile-menu").classList.add("open");
        mm_state = true;
    } else {
        document.querySelector(".menu-btn").classList.remove("open");
        document.querySelector(".mobile-menu").classList.remove("open");
        mm_state = false;
    }
}

// SESSION
$.ajax({
    dataType: "json",
    type: "POST",
    url: `${back_end}/session.php`,
    data: "req=1",
    success: (e) => {
        if (isNaN(e)) {
            $(".sdm-button").text("Minha Conta");
            $(".logout-button").removeClass("hidden");
        }
    },
});

// LOGOUT
function Logout() {
    $.ajax({
        dataType: "json",
        type: "POST",
        url: `${back_end}/session.php`,
        data: "req=2",
    });
    setTimeout(() => {
        sessionStorage.clear();
        location.reload();
    }, 1000);
}

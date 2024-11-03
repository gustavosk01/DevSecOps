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

// EXPAND CONTACT FORM
$(document).ready(() => {
    expand_form.on("click", () => {
        contact_section.toggleClass("hidden");
        expand_form.toggleClass("fa-plus fa-minus");
    });
});

// SEND MSG
contact_name.on("input", () => vName(contact_name));
contact_email.on("input", () => vEmail(contact_email));
contact_subject.on("input", () => vMessage(contact_subject));
contact_message.on("input", () => vMessage(contact_message));

contact.submit((e) => {
    e.preventDefault();

    $.ajax({
        dataType: "json",
        type: "POST",
        url: `${back_end}/contact.php`,
        data: contact.serialize(),
        beforeSend: () => {
            if (!isFormValid(contact)) return false;
        },
        complete: (e) => {
            e = e.responseJSON;
            contact_msg.text(e.message);

            if (e.code == 200) {
                setTimeout(() => {
                    location.reload();
                }, 1000);
            }
        },
    });
});

// jQUERY MASKS
$('input[id^="cpf-input"]').mask("000.000.000-00");
transfer_value.mask("000.000.000,00", { reverse: true });
signup_birth.mask("00/00/0000");
signup_phone.mask("(00) 00000-0000");
update_phone.mask("(00) 00000-0000");
otp_input.mask("000000");

// VALIDATION LISTENERS
image_input.on("input", () => vImage(image_input));
transfer_cpf.on("input", () => vCPF(transfer_cpf));
transfer_value.on("input", () => vMoney(transfer_value));
transfer_message.on("input", () => vMessage(transfer_message));
login_email.on("input", () => vEmail(login_email));
login_pass.on("input", () => vPass1(login_pass));
signup_cpf.on("input", () => vCPF(signup_cpf));
signup_name.on("input", () => vName(signup_name));
signup_birth.on("input", () => vDate(signup_birth));
signup_phone.on("input", () => vPhone(signup_phone));
update_phone.on("input", () => vPhone(update_phone));
signup_email.on("input", () => vEmail(signup_email));
update_email.on("input", () => vEmail(update_email));
signup_pass1.on("input", () => vPass1(signup_pass1));
signup_pass2.on("input", () => vPass2(signup_pass1, signup_pass2));
forgotpass1_cpf.on("input", () => vCPF(forgotpass1_cpf));
forgotpass2_pass1.on("input", () => vPass1(forgotpass2_pass1));
forgotpass2_pass2.on("input", () =>
    vPass2(forgotpass2_pass1, forgotpass2_pass2)
);
otp_input.on("input", () => vOTP(otp_input));

// SESSION HANDLER
if (document.cookie.length > 0) {
    $.ajax({
        dataType: "json",
        type: "GET",
        url: `${back_end}/session.php`,
        success: (e) => {
            let data = e.data;
            ChangeScreen(account, true);
            account_name.text(data.name);
            account_balance.text(data.money);
            update_email[0].placeholder = data.email;
            update_phone[0].placeholder = data.phone;
            getTransfers(data.transfers, data.cpf);
            if (data.hasImage) getImage();
        },
        error: (e) => {
            Logout();
        },
    });
} else {
    ChangeScreen(login);
}

// IMAGE
image_submit.on("click", () => image.submit());

image.submit((e) => {
    e.preventDefault();

    let formData = new FormData();
    formData.append("image", image_input.prop("files")[0]);

    $.ajax({
        dataType: "json",
        type: "POST",
        url: `${back_end}/image.php`,
        data: formData,
        contentType: false,
        processData: false,
        beforeSend: () => {
            if (!isFormValid(image)) return false;
        },
        complete: (e) => {
            e = e.responseJSON;
            image_msg.text(e.message);

            if (e.code == 200) {
                setTimeout(() => {
                    location.reload();
                }, 1000);
            }
        },
    });
});

// UPDATE INFO
update_info.submit((e) => {
    e.preventDefault();

    $.ajax({
        dataType: "json",
        type: "POST",
        url: `${back_end}/update_info.php`,
        data: update_info.serialize(),
        beforeSend: () => {
            if (!isFormValid(update_info)) return false;
        },
        complete: (e) => {
            e = e.responseJSON;
            update_msg.text(e.message);

            if (e.code == 200) {
                let data = e.data;
                update_email[0].placeholder = data.email;
                update_phone[0].placeholder = data.phone;
                setTimeout(() => {
                    location.reload();
                }, 1000);
            }
        },
    });
});

// DELETE ACCOUNT
delete_account.submit((e) => {
    e.preventDefault();

    $.ajax({
        dataType: "json",
        type: "DELETE",
        url: `${back_end}/update_info.php`,
        complete: (e) => {
            setTimeout(() => {
                location.reload();
            }, 1000);
        }
    });
});

// TRANSFER
transfer.submit((e) => {
    e.preventDefault();

    $.ajax({
        dataType: "json",
        type: "POST",
        url: `${back_end}/transfer.php`,
        data: transfer.serialize(),
        beforeSend: () => {
            if (!isFormValid(transfer)) return false;
        },
        complete: (e) => {
            e = e.responseJSON;
            transfer_msg.text(e.message);

            if (e.code == 200) {
                setTimeout(() => {
                    location.reload();
                }, 1000);
            }
        },
    });
});

// LOGIN
login.submit((e) => {
    e.preventDefault();
    login_hash.val(CryptoJS.SHA256(login_pass.val()));

    $.ajax({
        dataType: "json",
        type: "POST",
        url: `${back_end}/login.php`,
        data: login.serialize(),
        beforeSend: () => {
            if (!isFormValid(login)) return false;
        },
        complete: (e) => {
            e = e.responseJSON;
            login_msg.text(e.message);

            if (e.code == 200) {
                setTimeout(() => {
                    ChangeScreen($("#otp"));
                }, 1000);
            } else if (e.code == 202) {
                setTimeout(() => {
                    ChangeScreen($("#otp"));
                    getOTP();
                }, 1000);
            }
        },
    });
});

// SIGN UP
sign_up.submit((e) => {
    e.preventDefault();
    signup_hash.val(CryptoJS.SHA256(signup_pass2.val()));

    $.ajax({
        dataType: "json",
        type: "POST",
        url: `${back_end}/sign_up.php`,
        data: sign_up.serialize(),
        beforeSend: () => {
            if (!isFormValid(sign_up)) return false;
        },
        success: (e) => {
            signup_msg.text(e.message);

            if (e.code == 201) {
                setTimeout(() => {
                    ChangeScreen($("#otp"));
                    getOTP();
                }, 1000);
            }
        },
    });
});

// FORGOT PASS 1
forgot_pass1.submit((e) => {
    e.preventDefault();

    $.ajax({
        dataType: "json",
        type: "POST",
        url: `${back_end}/forgot_pass.php`,
        data: forgot_pass1.serialize(),
        beforeSend: () => {
            if (!isFormValid(forgot_pass1)) return false;
        },
        complete: (e) => {
            e = e.responseJSON;
            forgotpass1_msg.text(e.message);

            if (e.code == 200) {
                setTimeout(() => {
                    ChangeScreen($("#otp"));
                }, 1000);
            }
        },
    });
});

// FORGOT PASS 2
forgot_pass2.submit((e) => {
    e.preventDefault();
    forgotpass2_hash.val(CryptoJS.SHA256(forgotpass2_pass2.val()));

    $.ajax({
        dataType: "json",
        type: "POST",
        url: `${back_end}/forgot_pass.php`,
        data: forgot_pass2.serialize(),
        beforeSend: () => {
            if (!isFormValid(forgot_pass2)) return false;
        },
        complete: (e) => {
            e = e.responseJSON;
            forgotpass2_msg.text(e.message);

            if (e.code == 200) {
                location.reload();
            }
        },
    });
});

// OTP REGISTER
function getOTP() {
    $.ajax({
        dataType: "json",
        type: "GET",
        url: `${back_end}/otp.php`,
        success: (e) => {
            if (e.code == 200) {
                location.reload();
            } else if (e.code == 202) {
                otp_img.attr("src", e.data.qrCodeUri);
            }
        },
    });
}

// OTP
otp.submit((e) => {
    e.preventDefault();

    $.ajax({
        dataType: "json",
        type: "POST",
        url: `${back_end}/otp.php`,
        data: otp.serialize(),
        beforeSend: () => {
            if (!isFormValid(otp)) return false;
        },
        complete: (e) => {
            e = e.responseJSON;
            otp_msg.text(e.message);

            if (e.code == 200) {
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else if (e.code == 202) {
                ChangeScreen($("#forgot_pass2"));
            }
        },
    });
});

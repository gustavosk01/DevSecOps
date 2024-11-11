// FUNCTIONS
function ChangeScreen(screen, loggedIn = false) {
    $(".form-input").val("");
    $(".form-input").removeClass(["form-input-error", "form-input-success"]);
    $(".input-error-msg").text("");
    $(".form-error-msg").text("");
    $(".form").not("hidden").addClass("hidden");
    $(".my-account").not("hidden").addClass("hidden");
    
    const screens = Array.isArray(screen) ? screen : [screen];
    
    screens.forEach((s) => s.removeClass("hidden"));

    if (loggedIn) $(".my-account").removeClass("hidden");
}

function Logout() {
    $.ajax({
        dataType: "json",
        type: "DELETE",
        url: `${back_end}/session.php`,
    });
    setTimeout(() => {
        sessionStorage.clear();
        location.reload();
    }, 1000);
}

function getImage() {
    fetch(`${back_end}/image.php`).then((e) => {
        if (e.status == 404) return;
        e.blob().then((e) => {
            account_image.attr("src", URL.createObjectURL(e));
        });
    });
}

function getTransfers(transfers, cpf) {
    if (transfers.length == 0) {
        account_transfers.append(
            '<p class="form-error-msg">Nenhuma transação encontrada.</p>'
        );
    } else {
        transfers.forEach((transfer) => {
            let isSource = transfer.cpf_src == cpf;
            let title = isSource
                ? `Enviada para ${transfer.cpf_dst}`
                : `Recebida de ${transfer.cpf_src}`;
            let value = new Intl.NumberFormat("pt-BR", {
                style: "currency",
                currency: "BRL",
            }).format(transfer.amount);
            let date = new Intl.DateTimeFormat("pt-BR").format(
                new Date(transfer.date)
            );

            account_transfers.append(`
                <div class="transfer">
                    <p><b class="transfer-title">${title}</b></p>
                    <p><b class="transfer-value${
                        isSource ? '-src">-' : '-dst">'
                    }${value}</b></p>
                    <p class="transfer-date">${date}</p>
                    <p class="transfer-description"></p>
                </div>
            `);
            $(
                ".transfers-container .transfer:last-child .transfer-description"
            ).text(transfer.message);
        });
    }
}

// VALIDATION FUNCTIONS
function isFormValid(form) {
    let isFormValid = true;
    form.find(".form-error-msg").text("");
    form.find(".form-input").each((i, e) => {
        $(e).trigger("input");
        if (!$(e).hasClass("form-input-success")) isFormValid = false;
    });
    return isFormValid;
}

function vInput(input, validate, error_msg) {
    input.removeClass(["form-input-error", "form-input-success"]);
    if (validate) {
        input.addClass("form-input-success");
        input.parent().find(".input-error-msg").text("");
        return true;
    } else {
        input.addClass("form-input-error");
        input.parent().find(".input-error-msg").text(error_msg);
        return false;
    }
}

function vImage(input) {
    let files = input.prop("files");
    let file = files[0];
    if (!vInput(input, files.length == 1, "Selecione uma imagem."))
        return false;
}

function vMoney(input) {
    let regexMoney = /^\d{1,3}(.\d{3}){0,2}(,\d{2})/;
    return vInput(
        input,
        regexMoney.test(input.val()) &&
            parseFloat(input.val().replaceAll(".", "").replaceAll(",", ".")) <=
                parseFloat(
                    account_balance
                        .text()
                        .replaceAll(".", "")
                        .replaceAll(",", ".")
                ),
        "Valor inválido."
    );
}

function vMessage(input) {
    let regexDescription = /^.{0,255}$/;
    return vInput(
        input,
        regexDescription.test(input.val()),
        "Mensagem inválida (Max. 255 caracteres)."
    );
}

function vCPF(input) {
    let regexCPF = /^\d{3}\.\d{3}\.\d{3}-\d{2}$/;
    return vInput(input, regexCPF.test(input.val()), "XXX.XXX.XXX-XX");
}

function vName(input) {
    let regexName = /^(([A-zÀ-ú']{2,})\s?)+$/;
    return vInput(
        input,
        regexName.test(input.val()) && input.val().length <= 255,
        "Insira seu nome completo."
    );
}

function vDate(input) {
    let regexDate = /^\d{2}\/\d{2}\/\d{4}$/;
    return vInput(input, regexDate.test(input.val()), "dd/mm/yyyy");
}

function vPhone(input) {
    let regexPhone = /^\(\d{2}\) \d{5}-\d{4}$/;
    return vInput(input, regexPhone.test(input.val()), "(XX) XXXXX-XXXX");
}

function vEmail(input) {
    let regexEmail = /^[^@ ]+@[^@ ]+\.[^@ ]+$/;
    return vInput(input, regexEmail.test(input.val()), "E-mail inválido.");
}

function vPass1(input) {
    let regexPass =
        /^(?=.*?[a-z])(?=.*?\d)(?=.*?[#?!@$%^&*-]).{8,}$/;
    return vInput(
        input,
        regexPass.test(input.val()),
        "Minímo de 8 caracteres, ao menos uma letra minúscula, uma maiúscula, um caractere especial e um número."
    );
}

function vPass2(input1, input2) {
    return vInput(
        input2,
        input1.val() == input2.val() && input2.val() != "",
        "Senhas diferentes."
    );
}

function vOTP(input) {
    let regexOTP = /^\d{6}$/;
    return vInput(input, regexOTP.test(input.val()), "");
}
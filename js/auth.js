function validatePassword() {
    var password = document.getElementById("password").value;
    var confirmPassword = document.getElementById("confirm_password").value;
    if (password !== confirmPassword) {
        alert("Zadané heslá nie sú totožné.");
        return false;
    }
    return true;
}

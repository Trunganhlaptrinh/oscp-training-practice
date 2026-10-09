const $ = id => document.getElementById(id);

function showMessage(message, type = "error") {
    const messageBox = $("message");
    messageBox.textContent = message;
    messageBox.className = "message " + type;
}

$("login-form").addEventListener("submit", async event => {
    event.preventDefault();

    const username = $("username").value.trim();
    const password = $("password").value.trim();
    const button = event.target.querySelector("button[type='submit']");

    showMessage("");

    if (username === "") {
        showMessage("Username không được để trống");
        return;
    }

    if (password === "") {
        showMessage("Password không được để trống");
        return;
    }

    const formData = new FormData();
    formData.append("username", username);
    formData.append("password", password);

    button.disabled = true;

    try {
        const response = await fetch("../backend/login.php", {
            method: "POST",
            body: formData
        });

        const json = await response.json();

        if (!json.success) {
            throw new Error(json.message);
        }

        showMessage(json.message, "success");

        setTimeout(() => {
            window.location.href = "index.html";
        }, 500);
    } catch (error) {
        showMessage(
            error.message || "Không kết nối được tới server"
        );
    } finally {
        button.disabled = false;
    }
});
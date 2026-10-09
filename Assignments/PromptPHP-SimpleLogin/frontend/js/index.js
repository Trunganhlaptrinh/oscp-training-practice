const $ = id => document.getElementById(id);

async function loadSession() {
    try {
        const response = await fetch("../backend/login.php", {
            headers: { "Accept": "application/json" }
        });
        const json = await response.json();

        if (!response.ok || !json.success) {
            window.location.replace("login.html");
            return;
        }

        $("username").textContent = json.data.username;
    } catch {
        $("message").textContent = "Không kết nối được tới server";
        $("message").className = "message error";
    }
}

$("logout-btn").addEventListener("click", async event => {
    const button = event.currentTarget;
    button.disabled = true;

    try {
        const response = await fetch("../backend/logout.php", {
            method: "POST",
            headers: { "Accept": "application/json" }
        });
        const json = await response.json();

        if (!response.ok || !json.success) {
            throw new Error(json.message || "Đăng xuất không thành công");
        }

        window.location.replace("login.html");
    } catch (error) {
        $("message").textContent = error.message || "Không kết nối được tới server";
        $("message").className = "message error";
        button.disabled = false;
    }
});

loadSession();

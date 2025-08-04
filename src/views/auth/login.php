<div class="min-h-screen flex items-center justify-center">
  <div class="w-full max-w-md mx-auto bg-white rounded-2xl shadow-xl p-8 border border-gray-200">
    <div class="flex flex-col items-center mb-6">
      <img src="logo.png" alt="Logo" class="w-16 h-16 mb-4 rounded-xl shadow-md bg-gray-50 border border-gray-200">
      <h2 class="text-2xl font-bold text-gray-900 mb-2">Account Login</h2>
      <p class="text-gray-500 text-sm">Enter your credentials to access your dashboard.</p>
    </div>
    <form id="loginForm" class="space-y-5">
      <div>
        <label class="block mb-1 text-gray-700 font-medium" for="school_id">School ID / Username</label>
        <input
          type="text"
          name="school_id"
          id="school_id"
          class="w-full px-4 py-2 rounded-xl border border-gray-300 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-black text-gray-900"
          placeholder="Enter your ID or username"
          required
        >
      </div>
      <div>
        <label class="block mb-1 text-gray-700 font-medium" for="password">Password</label>
        <input
          type="password"
          name="password"
          id="password"
          class="w-full px-4 py-2 rounded-xl border border-gray-300 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-black text-gray-900"
          placeholder="Enter your password"
          required
        >
      </div>
      <button
        type="submit"
        class="w-full py-2 mt-2 bg-black text-white font-semibold rounded-xl hover:bg-gray-900 transition"
      >
        ⇨ Login
      </button>
      <div id="loginMessage" class="text-center text-sm mt-2"></div>
    </form>
  </div>
</div>

<script>
document.getElementById("loginForm").onsubmit = async function (e) {
  e.preventDefault();
  const formData = new FormData(this);
  const msg = document.getElementById("loginMessage");
  msg.textContent = "";
  msg.className = "text-center text-sm mt-2";

  try {
    const response = await fetch("../api/auth/login.php", {
      method: "POST",
      body: formData,
      credentials: "include"
    });

    if (!response.ok) throw new Error("Network error: " + response.status);

    const result = await response.json();
    msg.textContent = result.message;

    if (result.status === "success") {
      msg.className += " text-green-600";
      setTimeout(() => {
        window.location.href = "dashboard_mvc.php?role=" + result.role;
      }, 1200);
    } else {
      msg.className += " text-red-600";
    }

  } catch (error) {
    msg.textContent = "Login failed: " + error.message;
    msg.className += " text-red-600";
  }
};
</script>
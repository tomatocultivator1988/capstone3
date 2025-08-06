<?php if (isset($error) && $error): ?>
<div class="fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50">
    <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<?php if (isset($success) && $success): ?>
<div class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50">
    <?php echo htmlspecialchars($success); ?>
</div>
<?php endif; ?>

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100">
  <div class="w-full max-w-md mx-auto bg-white rounded-2xl shadow-xl p-8 border border-gray-200">
    <div class="flex flex-col items-center mb-6">
      <div class="w-16 h-16 mb-4 rounded-xl shadow-md bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
      </div>
      <h2 class="text-2xl font-bold text-gray-900 mb-2">Exam Management System</h2>
      <p class="text-gray-500 text-sm text-center">Enter your credentials to access the examination portal.</p>
    </div>
    <form id="loginForm" class="space-y-5">
      <div>
        <label class="block mb-1 text-gray-700 font-medium" for="school_id">School ID / Username</label>
        <input
          type="text"
          name="school_id"
          id="school_id"
          class="w-full px-4 py-2 rounded-xl border border-gray-300 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900"
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
          class="w-full px-4 py-2 rounded-xl border border-gray-300 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900"
          placeholder="Enter your password"
          required
        >
      </div>
      <button
        type="submit"
        class="w-full py-2 mt-2 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-600 hover:to-indigo-700 transition duration-200"
      >
        ⇨ Login to Exam Portal
      </button>
      <div id="loginMessage" class="text-center text-sm mt-2"></div>
    </form>
    
    <div class="mt-6 pt-6 border-t border-gray-200">
      <div class="text-center text-xs text-gray-500">
        <p>Role-based access:</p>
        <div class="flex justify-center space-x-4 mt-2">
          <span class="px-2 py-1 bg-red-100 text-red-700 rounded">Admin</span>
          <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded">Faculty</span>
          <span class="px-2 py-1 bg-green-100 text-green-700 rounded">Student</span>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById("loginForm").onsubmit = async function (e) {
  e.preventDefault();
  const formData = new FormData(this);
  const msg = document.getElementById("loginMessage");
  const submitBtn = this.querySelector('button[type="submit"]');
  
  msg.textContent = "";
  msg.className = "text-center text-sm mt-2";
  
  // Disable button and show loading
  submitBtn.disabled = true;
  submitBtn.innerHTML = "⏳ Logging in...";

  try {
    const response = await fetch("/api/auth/login", {
      method: "POST",
      body: formData,
      credentials: "include"
    });

    if (!response.ok) throw new Error("Network error: " + response.status);

    const result = await response.json();
    msg.textContent = result.message;

    if (result.status === "success") {
      msg.className += " text-green-600";
      submitBtn.innerHTML = "✅ Login Successful!";
      
              setTimeout(() => {
          window.location.href = "/dashboard?role=" + result.role;
        }, 1200);
    } else {
      msg.className += " text-red-600";
      submitBtn.disabled = false;
      submitBtn.innerHTML = "⇨ Login to Exam Portal";
    }

  } catch (error) {
    msg.textContent = "Login failed: " + error.message;
    msg.className += " text-red-600";
    submitBtn.disabled = false;
    submitBtn.innerHTML = "⇨ Login to Exam Portal";
  }
};
</script>
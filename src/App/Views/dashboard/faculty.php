<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Faculty Dashboard</h1>
        <p class="text-gray-600 mt-2">Manage your subjects and exams</p>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">My Subjects</p>
                    <p class="text-2xl font-bold text-gray-900" id="subjectCount">0</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">My Exams</p>
                    <p class="text-2xl font-bold text-gray-900" id="examCount">0</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Students</p>
                    <p class="text-2xl font-bold text-gray-900" id="studentCount">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Create New Exam</h3>
            <p class="text-gray-600 mb-4">Create and configure a new examination for your students.</p>
            <button class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                Create Exam
            </button>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Manage Questions</h3>
            <p class="text-gray-600 mb-4">Add, edit, or organize questions for your examinations.</p>
            <button class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-200">
                Manage Questions
            </button>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">View Results</h3>
            <p class="text-gray-600 mb-4">Review and analyze examination results and student performance.</p>
            <button class="w-full bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition duration-200">
                View Results
            </button>
        </div>
    </div>
</div>

<script>
// Load faculty dashboard data
document.addEventListener('DOMContentLoaded', function() {
    // Load subjects count
    fetch('/api/subjects/index.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('subjectCount').textContent = data.data.length;
            }
        })
        .catch(error => console.error('Error loading subjects:', error));

    // Load exams count
    fetch('/api/exams/index.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('examCount').textContent = data.data.length;
            }
        })
        .catch(error => console.error('Error loading exams:', error));

    // Load students count
    fetch('/api/users/index.php?action=by_role&role=student')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('studentCount').textContent = data.data.length;
            }
        })
        .catch(error => console.error('Error loading students:', error));

    setupLogout();
});

function setupLogout() {
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '/logout.php';
            }
        });
    }
}
</script>
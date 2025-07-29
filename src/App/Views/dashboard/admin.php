<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
        <p class="text-gray-600 mt-2">Manage users, subjects, and exams</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Users</p>
                    <p class="text-2xl font-semibold text-gray-900" id="totalUsers">0</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Subjects</p>
                    <p class="text-2xl font-semibold text-gray-900" id="totalSubjects">0</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Exams</p>
                    <p class="text-2xl font-semibold text-gray-900" id="totalExams">0</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Active Exams</p>
                    <p class="text-2xl font-semibold text-gray-900" id="activeExams">0</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Management -->
        <div class="bg-white rounded-lg shadow border border-gray-200">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">User Management</h3>
                <div class="space-y-3">
                    <button class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors" onclick="openModal('addUserModal')">
                        Add New User
                    </button>
                    <button class="w-full bg-gray-100 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors" onclick="viewUsers()">
                        View All Users
                    </button>
                </div>
            </div>
        </div>

        <!-- Subject Management -->
        <div class="bg-white rounded-lg shadow border border-gray-200">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Subject Management</h3>
                <div class="space-y-3">
                    <button class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition-colors" onclick="openModal('addSubjectModal')">
                        Add New Subject
                    </button>
                    <button class="w-full bg-gray-100 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors" onclick="viewSubjects()">
                        View All Subjects
                    </button>
                </div>
            </div>
        </div>

        <!-- Exam Management -->
        <div class="bg-white rounded-lg shadow border border-gray-200">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Exam Management</h3>
                <div class="space-y-3">
                    <button class="w-full bg-purple-600 text-white py-2 px-4 rounded-lg hover:bg-purple-700 transition-colors" onclick="openModal('addExamModal')">
                        Create New Exam
                    </button>
                    <button class="w-full bg-gray-100 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors" onclick="viewExams()">
                        View All Exams
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Load dashboard data
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardStats();
});

async function loadDashboardStats() {
    try {
        // Load users count
        const usersResponse = await fetch('/api/users/index.php');
        const usersData = await usersResponse.json();
        if (usersData.status === 'success') {
            document.getElementById('totalUsers').textContent = usersData.data.length;
        }

        // Load subjects count
        const subjectsResponse = await fetch('/api/subjects/index.php');
        const subjectsData = await subjectsResponse.json();
        if (subjectsData.status === 'success') {
            document.getElementById('totalSubjects').textContent = subjectsData.data.length;
        }

        // Load exams count
        const examsResponse = await fetch('/api/exams/index.php');
        const examsData = await examsResponse.json();
        if (examsData.status === 'success') {
            document.getElementById('totalExams').textContent = examsData.data.length;
            // Count active exams (you can modify this logic based on your needs)
            const activeCount = examsData.data.filter(exam => exam.status === 'active').length;
            document.getElementById('activeExams').textContent = activeCount;
        }
    } catch (error) {
        console.error('Error loading dashboard stats:', error);
    }
}

function openModal(modalId) {
    // Implementation for opening modals
    console.log('Opening modal:', modalId);
}

function viewUsers() {
    window.location.href = '/users';
}

function viewSubjects() {
    window.location.href = '/subjects';
}

function viewExams() {
    window.location.href = '/exams';
}
</script>
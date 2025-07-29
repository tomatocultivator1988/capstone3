<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Student Dashboard</h1>
        <p class="text-gray-600 mt-2">View your exams and results</p>
    </div>

    <!-- Student Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Available Exams</p>
                    <p class="text-2xl font-bold text-gray-900" id="availableExams">0</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Completed</p>
                    <p class="text-2xl font-bold text-gray-900" id="completedExams">0</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border border-gray-200">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Average Score</p>
                    <p class="text-2xl font-bold text-gray-900" id="averageScore">0%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Exams -->
    <div class="bg-white rounded-lg shadow border border-gray-200 mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Available Exams</h2>
        </div>
        <div class="p-6">
            <div id="examsList" class="space-y-4">
                <div class="text-center py-8 text-gray-500">
                    Loading exams...
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Results -->
    <div class="bg-white rounded-lg shadow border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Recent Results</h2>
        </div>
        <div class="p-6">
            <div id="resultsList" class="space-y-4">
                <div class="text-center py-8 text-gray-500">
                    Loading results...
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Load student dashboard data
document.addEventListener('DOMContentLoaded', function() {
    loadAvailableExams();
    loadRecentResults();
    loadStats();
});

function loadAvailableExams() {
    fetch('/api/exams/index.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const examsList = document.getElementById('examsList');
                if (data.data.length === 0) {
                    examsList.innerHTML = '<div class="text-center py-8 text-gray-500">No exams available</div>';
                    return;
                }

                examsList.innerHTML = data.data.map(exam => `
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                        <div>
                            <h3 class="font-semibold text-gray-900">${exam.exam_title}</h3>
                            <p class="text-sm text-gray-600">${exam.subject_name} (${exam.subject_code})</p>
                            <p class="text-xs text-gray-500">Duration: ${exam.duration} minutes</p>
                        </div>
                        <div class="flex space-x-2">
                            <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition duration-200">
                                Take Exam
                            </button>
                        </div>
                    </div>
                `).join('');
            }
        })
        .catch(error => {
            console.error('Error loading exams:', error);
            document.getElementById('examsList').innerHTML = '<div class="text-center py-8 text-red-500">Error loading exams</div>';
        });
}

function loadRecentResults() {
    // This would typically fetch from an exam_results endpoint
    const resultsList = document.getElementById('resultsList');
    resultsList.innerHTML = '<div class="text-center py-8 text-gray-500">No results available yet</div>';
}

function loadStats() {
    // Load basic stats
    fetch('/api/exams/index.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('availableExams').textContent = data.data.length;
            }
        })
        .catch(error => console.error('Error loading stats:', error));
}
</script>
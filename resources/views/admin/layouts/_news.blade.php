<style>
    /* Existing styles */
    .bg-header-custum {
        background-color:#10332C; /* Desired color */
        color: #fff; /* Ensure text color contrasts well with the background */
        padding: 10px 20px; /* Add some padding */
        position: relative; /* Ensure the moving news stays within the header */
        overflow: hidden; /* Hide overflow content */
        height: 60px; /* Set a fixed height */
        display: flex; /* Use flexbox to center content */
        align-items: center; /* Center items vertically */
        font-family: Arial, sans-serif; /* Use a clean font */
    }

    .news-ticker {
        display: flex;
        align-items: center;
        position: absolute;
        white-space: nowrap;
        will-change: transform;
        animation: moveNews 15s linear infinite;
    }

    .news-item {
        display: flex;
        align-items: center;
        margin-right: 40px; /* Space between items */
    }

    .news-item h4, .news-item p {
        margin: 0 10px; /* Space around text */
    }

    .news-item i {
        margin-right: 5px; /* Space between icon and text */
    }

    @keyframes moveNews {
        0% {
            transform: translateX(100%);
        }
        100% {
            transform: translateX(-100%);
        }
    }
</style>

<section class="card-header bg-header-custum text-white">
    <div class="news-ticker">
        <div class="news-item">
            <i class="fas fa-chart-bar"></i>
            <h4>{{ __('words.statistics') }}</h4>
        </div>
        <div class="news-item">
            <i class="fas fa-users"></i>
            <p>{{ __('words.total_employees') }}: {{ $totalEmployees }}</p>
        </div>
        <div class="news-item">
            <i class="fas fa-file-contract"></i>
            <p>{{ __('words.total_contracts') }}: {{ $totalContracts }}</p>
        </div>
        <div class="news-item">
            <i class="fas fa-user"></i>
            <p>{{ __('words.total_users') }}: {{ $totalUsers }}</p>
        </div>
        <div class="news-item">
            <i class="fas fa-user"></i>
            <p>{{ __('words.total_applications') }}: {{ $totalApplications }}</p>
        </div>
    </div>
</section>

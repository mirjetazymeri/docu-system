
document.addEventListener('DOMContentLoaded', function() {
    let inactivityTime = function (timeoutMinutes) {
        let time;
        const timerElement = document.getElementById('timer');
        let minutes = timeoutMinutes;
        let seconds = 0;

        function updateTimer() {
            if (seconds === 0) {
                if (minutes === 0) {
                    logout();
                    return;
                }
                minutes--;
                seconds = 59;
            } else {
                seconds--;
            }

            const minutesStr = minutes.toString().padStart(2, '0');
            const secondsStr = seconds.toString().padStart(2, '0');
            timerElement.textContent = `${minutesStr}:${secondsStr}`;
        }

        window.onload = resetTimer;
        document.onmousemove = resetTimer;
        document.onkeypress = resetTimer;

        function logout() {
            window.location.href = "/admin/logout";  // Use the correct route to handle logout
        }

        function resetTimer() {
            clearTimeout(time);
            time = setTimeout(logout, timeoutMinutes * 60000);  // timeoutMinutes in milliseconds
            minutes = timeoutMinutes;
            seconds = 0;
        }

        setInterval(updateTimer, 1000);
    };

    // Fetch the timeoutMinutes from a data attribute on the timer element
    const timeoutMinutes = document.getElementById('timer').dataset.timeout;
    inactivityTime(parseInt(timeoutMinutes));
});

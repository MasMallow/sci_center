document.addEventListener("DOMContentLoaded", function () {
    const toast = document.querySelector(".toast");
    const closeIcon = document.querySelector(".close");
    const progress = document.querySelector(".progress");

    // Add active class to trigger the animation
    setTimeout(() => {
        toast.classList.add("active");
        progress.classList.add("active");
    }); // Delay slightly to ensure the DOM is ready

    // Remove active class after a timeout
    setTimeout(() => {
        toast.classList.remove("active");
    }, 3000); // 5s + 100ms delay

    setTimeout(() => {
        progress.classList.remove("active");
    }, 3300); // 5.3s + 100ms delay

    closeIcon.addEventListener("click", () => {
        toast.classList.remove("active");
        setTimeout(() => {
            progress.classList.remove("active");
        }, 300);
    });
});

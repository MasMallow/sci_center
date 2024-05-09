document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.form');
    const paginationNumbers = document.querySelectorAll('.pagination .number');
    const prevBtn = document.querySelector('.btn_prev');
    const nextBtn = document.querySelector('.btn_next');

    let currentFormIndex = 0;

    function showForm(index) {
        forms.forEach((form, i) => {
            if (i === index) {
                form.classList.add('active');
            } else {
                form.classList.remove('active');
            }
        });

        paginationNumbers.forEach((number, i) => {
            if (i === index) {
                number.classList.add('active');
            } else {
                number.classList.remove('active');
            }
        });

        if (index === 0) {
            prevBtn.style.display = 'none';
        } else {
            prevBtn.style.display = 'inline-block';
        }

        if (index === forms.length - 1) {
            nextBtn.style.display = 'none';
        } else {
            nextBtn.style.display = 'inline-block';
        }
    }

    nextBtn.addEventListener('click', function () {
        if (currentFormIndex < forms.length - 1) {
            currentFormIndex++;
            showForm(currentFormIndex);
        }
    });

    prevBtn.addEventListener('click', function () {
        if (currentFormIndex > 0) {
            currentFormIndex--;
            showForm(currentFormIndex);
        }
    });

    paginationNumbers.forEach((number, i) => {
        number.addEventListener('click', function () {
            currentFormIndex = i;
            showForm(currentFormIndex);
        });
    });
});

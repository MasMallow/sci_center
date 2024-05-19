document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.add_MET_section_form');
    const paginationNumbers = document.querySelectorAll('.pagination .number');
    const prevBtns = document.querySelectorAll('.btn_prev');
    const nextBtns = document.querySelectorAll('.btn_next');
    const addMET = document.querySelector('.add_MET');

    let currentFormIndex = 0;

    function showForm(index) {
        forms.forEach((form, i) => {
            form.classList.toggle('active', i === index);
        });

        paginationNumbers.forEach((number, i) => {
            number.classList.toggle('active', i === index);
        });

        prevBtns.forEach(btn => btn.style.display = index === 0 ? 'none' : 'inline-block');
        nextBtns.forEach(btn => btn.style.display = index === forms.length - 1 ? 'none' : 'inline-block');
        addMET.style.margin = index === 0 ? '3rem auto' : '9.5rem auto';
    }

    nextBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            if (currentFormIndex < forms.length - 1) {
                currentFormIndex++;
                showForm(currentFormIndex);
            }
        });
    });

    prevBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            if (currentFormIndex > 0) {
                currentFormIndex--;
                showForm(currentFormIndex);
            }
        });
    });

    paginationNumbers.forEach((number, i) => {
        number.addEventListener('click', function () {
            currentFormIndex = i;
            showForm(currentFormIndex);
        });
    });

    let imgInput = document.getElementById('imgInput');
    let previewImg = document.getElementById('previewImg');
    let fileChosenImg = document.getElementById('file-chosen-img');

    imgInput.addEventListener('change', function () {
        const [file] = imgInput.files;
        if (file) {
            previewImg.src = URL.createObjectURL(file);
            fileChosenImg.textContent = file.name;
        }
    });

    showForm(currentFormIndex); // Initialize the first form view
});
document.addEventListener('DOMContentLoaded', function () {
    const forms = document.querySelectorAll('.add_MET_section_form');
    const paginationNumbers = document.querySelectorAll('.pagination .number');
    const prevBtn = document.querySelector('.btn_prev');
    const nextBtn = document.querySelector('.btn_next');
    const addMET = document.querySelector('.add_MET');

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
            addMET.style.margin = '9.5rem auto';
        }

        if (index === forms.length - 1) {
            nextBtn.style.display = 'none';
        } else {
            nextBtn.style.display = 'inline-block';
            addMET.style.margin = '3rem auto';
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

let imgInput = document.getElementById('imgInput');
let previewImg = document.getElementById('previewImg');

imgInput.onchange = evt => {
    const [file] = imgInput.files;
    if (file) {
        previewImg.src = URL.createObjectURL(file)
    }
}
// เลือกไฟล์รูป
document.getElementById("imgInput").addEventListener("change", function () {
    const fileName = this.files[0].name;
    document.getElementById("file-chosen-img").textContent = fileName;
});

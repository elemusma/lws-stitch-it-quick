let openModalArrowFunction = (elem) => {
    elemID = elem.getAttribute('id');
    elemClose = document.querySelector('.modal-content.' + elemID + ' .close');
    elemOverlay = document.querySelector('.modal-content.' + elemID + ' .bg-overlay');
    elemContent = document.querySelector('.modal-content.' + elemID);
    elemContent.classList.add('activate-modal');

    elemClose.addEventListener('click', function(){
        elemContent.classList.remove('activate-modal');
    });
    elemOverlay.addEventListener('click', function(){
        elemContent.classList.remove('activate-modal');
    });
}

let openModalBtn = document.querySelectorAll('.open-modal');

for ( i = 0; i < openModalBtn.length; i++ ) {
    openModalBtn[i].addEventListener('click', openModal);
    function openModal() {
        openModalArrowFunction(this);
    }
    function closeModal() {
        closeModalArrowFunction(this);
    }
}

document.onkeydown = function(evt) {
    evt = evt || window.event;
    if (evt.keyCode == 27) {
        modalContent = document.querySelectorAll('.modal-content');
        for ( i = 0; i < modalContent.length; i++){
            if(modalContent[i].classList.contains('activate-modal')){
                modalContent[i].classList.remove('activate-modal');
            }
        }
    }
};
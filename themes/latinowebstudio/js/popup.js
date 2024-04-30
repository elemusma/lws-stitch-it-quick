// Get all buttons that open modals
var modalBtns = document.getElementsByClassName("openModalBtn");

// Loop through all buttons and add click event listeners
for (var i = 0; i < modalBtns.length; i++) {
  modalBtns[i].onclick = function() {
    var modalId = this.getAttribute('data-modal-id');
    var modal = document.getElementById(modalId);
    // modal.style.display = "block";
    console.log(modal);
    modal.classList.add('active');
  }
}

// Get all elements with class="close" (close buttons)
var closeBtns = document.getElementsByClassName("close");

// Loop through all close buttons and add click event listeners
for (var i = 0; i < closeBtns.length; i++) {
  closeBtns[i].onclick = function() {
    var modal = this.parentElement.parentElement;
    // modal.style.display = "none";
    // modal.style.opacity = 0;
    console.log(modal);
    modal.classList.remove('active');
  }
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  
  console.log(event.target);
  if (event.target.tagName === 'A') {
    // Your code here for handling clicks on <a> elements
    // console.log('hello');
    document.querySelector('#mobileMenu').classList.remove('active');
}
  if (event.target.classList.contains('modal') || event.target.classList.contains('modal-content-menu')) {
    event.target.classList.remove('active');
    document.querySelector('#mobileMenu').classList.remove('active');
    
  }
  // if () {
  //   event.target.classList.remove('active');
  // }
}

// let openModalArrowFunction = (elem) => {
//     elemID = elem.getAttribute('id');
//     elemClose = document.querySelector('.modal-content.' + elemID + ' .close');
//     elemOverlay = document.querySelector('.modal-content.' + elemID + ' .bg-overlay');
//     elemContent = document.querySelector('.modal-content.' + elemID);
//     elemContent.classList.add('activate-modal');

//     elemClose.addEventListener('click', function(){
//         elemContent.classList.remove('activate-modal');
//     });
//     elemOverlay.addEventListener('click', function(){
//         elemContent.classList.remove('activate-modal');
//     });
// }

// let openModalBtn = document.querySelectorAll('.open-modal');

// for ( i = 0; i < openModalBtn.length; i++ ) {
//     openModalBtn[i].addEventListener('click', openModal);
//     function openModal() {
//         openModalArrowFunction(this);
//     }
//     function closeModal() {
//         closeModalArrowFunction(this);
//     }
// }

// document.onkeydown = function(evt) {
//     evt = evt || window.event;
//     if (evt.keyCode == 27) {
//         modalContent = document.querySelectorAll('.modal-content');
//         for ( i = 0; i < modalContent.length; i++){
//             if(modalContent[i].classList.contains('activate-modal')){
//                 modalContent[i].classList.remove('activate-modal');
//             }
//         }
//     }
// };
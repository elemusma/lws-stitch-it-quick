let menuHeight = document.querySelector('ul#menu-main-menu');
let navMenu = document.querySelector('#navItems');
let navMenuToggle = document.querySelector('#navToggle');
let navMenuOverlay = document.querySelector('#navMenuOverlay');
let navMenuClose = document.querySelector('#navMenuClose');
let header = document.querySelector('header');
let blankSpace = document.querySelector('.blank-space');

let logoMain = document.querySelector('#logo-main')
let logoSecondary = document.querySelector('#logo-secondary')

window.addEventListener("scroll", parallaxEffect);

function parallaxEffect() {
    blankSpace.style.height = (((header.offsetHeight) - 1) + "px");
    header.classList.add('position-fixed');
    header.classList.remove('position-relative');

    // if (scrollY < 299) {
    //     logoMain.style.width = "275px"
    // } else if (scrollY > 300) {
    //     logoMain.style.width = "205px"
    // }
}

// closes menu on menu item clicked, for anchor links
let menuItems = document.querySelectorAll('.menu-item');

for (i = 0; i < menuItems.length; i++) {
    menuItems[i].addEventListener('click', closeMenu);
}
// ###################################################

navMenuClose.addEventListener('click', closeMenu);
navMenuOverlay.addEventListener('click', closeMenu);
navMenuToggle.addEventListener('click', openMenu);

function openMenu() {
    setTimeout(function () {
        navMenu.classList.add('activate');
    }, 500);
    navMenuToggle.classList.add('activate');
    navMenuOverlay.classList.add('activate');
}

function closeMenu() {
    navMenu.classList.remove('activate');
    navMenuToggle.classList.remove('activate');
    setTimeout(function () {
        navMenuOverlay.classList.remove('activate');
    }, 500);
}

// $('#navToggle').click(function(event) {
// $('#navToggle').toggleClass('activate');
// $('#navItems').toggleClass('activate');
// if(navMenu.classList.contains('activate')){
// // navMenu.style.height = menuHeight.offsetHeight+"px";
// // navMenu.style.height = "100vh";
// } else {
// // navMenu.style.height = "0px";
// }
// });

// for dropdown menus on desktop
function variables() {
    menuItemHasChildren = document.querySelectorAll('.menu-item-has-children');
    menuItemHasChildrenMobile = document.querySelectorAll('#navItems .menu-item-has-children');
    desktopWidth = window.matchMedia('(min-width: 1200px');
}
variables();


// if(desktopWidth.matches){

function mouseOver() {
    for (i = 0; i < menuItemHasChildren.length; i++) {
        arrow = document.createElement("div");
        if (window.matchMedia('(max-width: 1199px').matches) {

        } else {
            arrow.className = "dropdown-arrow position-absolute bg-light inactive";
            menuItemHasChildren[i].append(arrow);
            menuItemHasChildren[i].addEventListener('mouseover', activeDropdown);
            menuItemHasChildren[i].addEventListener('mouseout', inActiveDropdown);
        }
        // console.log(arrow);
        // arrow.addEventListener('click',activeDropdownClicked);
    }
};
mouseOver();

function clickMobile() {
    for (i = 0; i < menuItemHasChildrenMobile.length; i++) {
        arrowMobile = document.createElement("div");
        arrowMobile.className = "dropdown-arrow position-absolute bg-light inactive arrow-mobile mobile-hidden";
        menuItemHasChildrenMobile[i].append(arrowMobile);
        console.log(arrowMobile);
        console.log(this.menuItemHasChildrenMobile[i]);
        console.log(this.menuItemHasChildrenMobile[i].querySelector('a'));
        specificLink = this.menuItemHasChildrenMobile[i].querySelector('a')
        // this.addEventListener('click',activeDropdownClicked);
        // menuItemHasChildrenMobile[i].addEventListener('click',activeDropdownClicked);
        // arrowMobile.addEventListener('click',activeDropdownClicked);
        // specificLink.addEventListener('click',inActiveDropdownClicked);
        this.menuItemHasChildrenMobile[i].addEventListener('click', activeDropdownClicked);
        // arrow.addEventListener('click',activeDropdownClicked);
    }
    function inActiveDropdownClicked() {
        // alert('link clicked');
        // console.log(this);
        // console.log(this.parentNode);
        // console.log(this.parentNode.childNodes[2]);
        subMenuMobile = this.parentNode.childNodes[2];
        subMenuMobile.style.height = '0px!important';
        // subMenuMobile.classList.add('another-class');
        // if(!subMenuMobile.classList.contains('another-class')){
        //     subMenuMobile.classList.add('another-class');
        // }
    }
    function activeDropdownClicked() {
        // alert('clicked');
        // console.log(this);
        // console.log(this.querySelector('.sub-menu'));
        // console.log(this.parentNode);
        // console.log(this.previousElementSibling);
        // subMenuMobile = this.parentNode;
        // subMenuMobile = this.previousElementSibling;
        subMenuMobile = this.querySelector('.sub-menu');
        // console.log(subMenuMobile);
        // this.classList.add('active-dropdown');
        if (!subMenuMobile.classList.contains('active-sub-menu')) {
            // alert('clicked to activate');
            subMenuMobile.classList.add('active-sub-menu');
            subMenuMobile.style.height = subMenuMobile.children[0].offsetHeight * subMenuMobile.children.length + 'px';
        } else if (subMenuMobile.classList.contains('active-sub-menu')) {
            // alert('clicked to deactivate');
            subMenuMobile.classList.remove('active-sub-menu');
            subMenuMobile.style.height = '0px';
        }
    }

};
clickMobile();

function activeDropdown() {
    subMenu = this.querySelector('.sub-menu');
    if (!subMenu.classList.contains('active-sub-menu')) {
        subMenu.classList.add('active-sub-menu');
        subMenu.style.height = subMenu.children[0].offsetHeight * subMenu.children.length + 'px';
    }
}
function inActiveDropdown() {
    subMenu = this.querySelector('.sub-menu');
    if (subMenu.classList.contains('active-sub-menu')) {
        subMenu.classList.remove('active-sub-menu');
        subMenu.style.height = '0px';
    }
}

// }
document.addEventListener('DOMContentLoaded', function() {
    var form = document.querySelector('.variations_form');
    var checkbox = document.getElementById('myCheckbox');
    var checkboxError = document.getElementById('checkboxError');

    form.addEventListener('submit', function(event) {
        // Check if the checkbox is checked
        if (!checkbox.checked) {
            // Prevent form submission
            event.preventDefault();
            // Show error message
            checkboxError.style.display = 'block';
        } else {
            // Hide error message if checkbox is checked
            checkboxError.style.display = 'none';
        }
    });
});


// function checkVariantValues() {
// let selectedValues = false;
// const variationColor = document.querySelector('#color');
// const variationSize = document.querySelector('#size');

// // let variationMaterial = document.querySelector('.single_variation_wrap');
// const variationMaterial = document.querySelector('#yith-wapo-block-1');
// console.log(variationMaterial);
// // variationMaterial.classList.add('not-active');
// variationMaterial.style.opacity = 0;

// variationColor.addEventListener('change',function() {
//     if (variationColor.value !== "" && variationSize.value !== "") {
//         selectedValues = true;
//         // showVariationMaterial();
//         variationMaterial.classList.add('active');
//         variationMaterial.classList.remove('not-active');
//     } else {
//         selectedValues = false;
//         console.log('no value selected');
//         // showVariationMaterial();
//         variationMaterial.classList.add('not-active');
//         variationMaterial.classList.remove('active');
//     }
//     // console.log(variationColor.value);
//     // console.log(selectedValues);
// });
// variationSize.addEventListener('change',function() {
//     if (variationSize.value !== "" && variationColor.value !== "") {
//         selectedValues = true;
//         // showVariationMaterial();
//         variationMaterial.classList.add('active');
//         variationMaterial.classList.remove('not-active');
//     } else {
//         selectedValues = false;
//         console.log('no value selected');
//         variationMaterial.classList.add('not-active');
//         variationMaterial.classList.remove('active');
//         // showVariationMaterial();
//     }
//     // console.log(variationSize.value);
//     // console.log(selectedValues);
// });

// // selectedValues.addEventListener('change', function(){
// //     console.log('hello')
// // })

// // function showVariationMaterial() {
// //     console.log(selectedValues);
// //     if (selectedValues) {
// //         variationMaterial.classList.add('active');
// //         console.log('hello');
// //     } else if(!selectedValues) {
// //         console.log('bye');
// //         variationMaterial.classList.remove('active');
// //     }
// //     console.log(selectedValues);
// // }


// }

// checkVariantValues();
previous = document.querySelector('#previous small');
next = document.querySelector('#next small');
previousLink = document.querySelector('#previous a');
nextLink = document.querySelector('#next a');

// console.log(previous.innerHTML);
// console.log(next.innerHTML);
// console.log(previousLink);
// console.log(nextLink);

if(previousLink == null ){
    previous.style.display="none";
}
if(nextLink == null ){
    next.style.display="none";
}